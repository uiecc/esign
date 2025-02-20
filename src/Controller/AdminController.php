<?php

namespace App\Controller;

use App\Entity\Candidate;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Options;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Centres avec le nombre de candidats par centre
        $centersQuery = $entityManager->createQuery(
            'SELECT
                c.examinationCenter,
                COUNT(c.id) as candidateCount,
                SUM(CASE WHEN c.sexe IN (\'M\', \'Masculin\', \'Male\') THEN 1 ELSE 0 END) as maleCount,
                SUM(CASE WHEN c.sexe IN (\'F\', \'Féminin\', \'Female\') THEN 1 ELSE 0 END) as femaleCount,
                SUM(CASE WHEN c.language = \'Anglais\' THEN 1 ELSE 0 END) as anglophones,
                SUM(CASE WHEN c.language = \'Français\' THEN 1 ELSE 0 END) as francophones
            FROM App\Entity\Candidate c
            GROUP BY c.examinationCenter'
        );
        $rawCenters = $centersQuery->getResult();

        // Normalize and aggregate centers in PHP
        $centers = [];
        foreach ($rawCenters as $center) {
            $centerName = $this->normalizeCenterName($center['examinationCenter']);
            if (!isset($centers[$centerName])) {
                $centers[$centerName] = [
                    'examinationCenter' => $centerName,
                    'candidateCount' => 0,
                    'maleCount' => 0,
                    'femaleCount' => 0,
                    'anglophones' => 0,
                    'francophones' => 0
                ];
            }
            $centers[$centerName]['candidateCount'] += $center['candidateCount'];
            $centers[$centerName]['maleCount'] += $center['maleCount'];
            $centers[$centerName]['femaleCount'] += $center['femaleCount'];
            $centers[$centerName]['anglophones'] += $center['anglophones'];
            $centers[$centerName]['francophones'] += $center['francophones'];
        }
        $centers = array_values($centers);

        // Filières avec le nombre de candidats par filière
        $fieldsQuery = $entityManager->createQuery(
            'SELECT c.field, COUNT(c.id) as candidateCount
             FROM App\Entity\Candidate c
             GROUP BY c.field'
        );
        $fields = $fieldsQuery->getResult();


        // Distribution des langues par centre
        $studentLanguagePerCentersQuery = $entityManager->createQuery(
            'SELECT c.examinationCenter, c.language, COUNT(c.id) as candidateCount
             FROM App\Entity\Candidate c
             GROUP BY c.examinationCenter, c.language'
        );
        $rawLanguagePerCenters = $studentLanguagePerCentersQuery->getResult();


        // Normalize and aggregate language distribution
        $studentLanguagePerCenters = [];
        foreach ($rawLanguagePerCenters as $row) {
            $centerName = $this->normalizeCenterName($row['examinationCenter']);
            $key = $centerName . '_' . $row['language'];
            if (!isset($studentLanguagePerCenters[$key])) {
                $studentLanguagePerCenters[$key] = [
                    'examinationCenter' => $centerName,
                    'language' => $row['language'],
                    'candidateCount' => 0
                ];
            }
            $studentLanguagePerCenters[$key]['candidateCount'] += $row['candidateCount'];
        }
        $studentLanguagePerCenters = array_values($studentLanguagePerCenters);


        // Types d'admission avec le nombre de candidats
        $studentAdmissionTypeQuery = $entityManager->createQuery(
            'SELECT c.admissionType, COUNT(c.id) as candidateCount
             FROM App\Entity\Candidate c
             GROUP BY c.admissionType'
        );
        $studentAdmissionType = $studentAdmissionTypeQuery->getResult();


        // Nombre de candidats par filière et nationalité
        $candidatePerFieldPerCountryQuery = $entityManager->createQuery(
            'SELECT c.nationality, c.field, COUNT(c.id) as candidateCount
             FROM App\Entity\Candidate c
             GROUP BY c.nationality, c.field'
        );
        $candidatePerFieldPerCountry = $candidatePerFieldPerCountryQuery->getResult();

        // Nombre total de candidats par pays - VERSION AMÉLIORÉE
        $nationalityQuery = $entityManager->createQuery(
            'SELECT 
                COALESCE(c.nationality, \'Non spécifié\') as nationality,
                COALESCE(c.examinationCenter, \'Non spécifié\') as examinationCenter,
                COUNT(c.id) as totalCandidates
             FROM App\Entity\Candidate c
             WHERE c.nationality IS NOT NULL 
             AND c.examinationCenter IS NOT NULL
             GROUP BY c.nationality, c.examinationCenter
             ORDER BY c.nationality ASC'
        );


        $nationality = [];
        foreach ($nationalityQuery->getResult() as $row) {
            $originalNationality = $row['nationality'];
            $nationalityKey = strtolower(trim($originalNationality));
            $centerName = $this->normalizeCenterName($row['examinationCenter']);

            // Gestion étendue des variations du Cameroun
            if (in_array($nationalityKey, ['cameroon', 'cameroun', 'cameroons', 'république du cameroun'], true)) {
                $nationalityKey = 'cameroun';
                $displayName = 'Cameroun';
            } else {
                $displayName = $originalNationality;
            }


            if (!isset($nationality[$nationalityKey])) {
                $nationality[$nationalityKey] = [
                    'nationality' => $displayName,
                    'totalCandidates' => 0,
                    'centers' => []
                ];
            }

            // Gestion des centres
            $centerFound = false;
            foreach ($nationality[$nationalityKey]['centers'] as &$center) {
                if (strtolower($center['examinationCenter']) === strtolower($centerName)) {
                    $center['candidateCount'] += (int)$row['totalCandidates'];
                    $centerFound = true;
                    break;
                }
            }

            if (!$centerFound) {
                $nationality[$nationalityKey]['centers'][] = [
                    'examinationCenter' => $centerName,
                    'candidateCount' => (int)$row['totalCandidates']
                ];
            }

            $nationality[$nationalityKey]['totalCandidates'] += (int)$row['totalCandidates'];
        }

        // Tri des centres par nombre de candidats
        foreach ($nationality as &$nat) {
            usort($nat['centers'], function ($a, $b) {
                return $b['candidateCount'] - $a['candidateCount'];
            });
        }


        $nationality = array_values($nationality);


        // Statistics for regions
        $regionStatsQuery = $entityManager->createQuery(
            'SELECT 
        COALESCE(c.region, \'Non spécifié\') as region,
        COUNT(c.id) as candidateCount,
        (COUNT(c.id) * 100.0 / (SELECT COUNT(sc.id) FROM App\Entity\Candidate sc)) as percentage
     FROM App\Entity\Candidate c
     GROUP BY c.region
     ORDER BY candidateCount DESC'
        );
        $regionStats = $regionStatsQuery->getResult();

        // Statistics for departments
        $departmentStatsQuery = $entityManager->createQuery(
            'SELECT 
                COALESCE(c.region, \'Non spécifié\') as region,
                COALESCE(c.depertement, \'Non spécifié\') as department,
                COUNT(c.id) as candidateCount,
                (COUNT(c.id) * 100.0 / (SELECT COUNT(sc.id) FROM App\Entity\Candidate sc)) as percentage
            FROM App\Entity\Candidate c
            GROUP BY c.region, c.depertement
            ORDER BY c.region ASC, candidateCount DESC'
        );
        $departmentStats = $departmentStatsQuery->getResult();

        // Organize departments by region
        $organizedDepartmentStats = [];
        foreach ($departmentStats as $stat) {
            if (!isset($organizedDepartmentStats[$stat['region']])) {
                $organizedDepartmentStats[$stat['region']] = [];
            }
            $organizedDepartmentStats[$stat['region']][] = $stat;
        }



        // Add this query to your controller
        $nationalityRegionQuery = $entityManager->createQuery(
            'SELECT 
        COALESCE(c.nationality, \'Non spécifié\') as nationality,
        COALESCE(c.region, \'Non spécifié\') as region,
        COALESCE(c.depertement, \'Non spécifié\') as department,
        COUNT(c.id) as candidateCount,
        (COUNT(c.id) * 100.0 / (
            SELECT COUNT(sc.id) 
            FROM App\Entity\Candidate sc 
            WHERE sc.nationality = c.nationality
        )) as percentage
    FROM App\Entity\Candidate c
    WHERE c.nationality IS NOT NULL
    GROUP BY c.nationality, c.region, c.depertement
    ORDER BY c.nationality ASC, c.region ASC, candidateCount DESC'
        );

        $rawNationalityStats = $nationalityRegionQuery->getResult();

        // Process the results to create a hierarchical structure
        $nationalityRegionStats = [];
        foreach ($rawNationalityStats as $row) {
            $originalNationality = $row['nationality'];
            $nationalityKey = strtolower(trim($originalNationality));

            // Normalize nationality names
            if (in_array($nationalityKey, ['Cameroon', 'Cameroun', 'cameroon','cameroun',], true)) {
                $nationalityKey = 'Cameroun';
                $displayName = 'Cameroun';
            } else {
                $displayName = $originalNationality;
            }

            // Initialize nationality if not exists
            if (!isset($nationalityRegionStats[$nationalityKey])) {
                $nationalityRegionStats[$nationalityKey] = [
                    'nationality' => $displayName,
                    'totalCandidates' => 0,
                    'regions' => []
                ];
            }

            // Initialize region if not exists
            if (!isset($nationalityRegionStats[$nationalityKey]['regions'][$row['region']])) {
                $nationalityRegionStats[$nationalityKey]['regions'][$row['region']] = [
                    'name' => $row['region'],
                    'candidateCount' => 0,
                    'departments' => []
                ];
            }

            // Add department data
            $nationalityRegionStats[$nationalityKey]['regions'][$row['region']]['departments'][] = [
                'name' => $row['department'],
                'candidateCount' => $row['candidateCount'],
                'percentage' => $row['percentage']
            ];

            // Update counts
            $nationalityRegionStats[$nationalityKey]['regions'][$row['region']]['candidateCount'] += $row['candidateCount'];
            $nationalityRegionStats[$nationalityKey]['totalCandidates'] += $row['candidateCount'];
        }

        // Convert to array and sort
        $nationalityRegionStats = array_values($nationalityRegionStats);


        return $this->render('admin/index.html.twig', [
            'centers' => $centers,
            'fields' => $fields,
            'nationality' => $nationality,
            'studentLanguagePerCenters' => $studentLanguagePerCenters,
            'studentAdmissionTypeCount' => $studentAdmissionType,
            'candidatePerFieldPerCountryCount' => $candidatePerFieldPerCountry,
            'regionStats' => $regionStats,
            'departmentStats' => $organizedDepartmentStats,
            'nationalityRegionStats' => $nationalityRegionStats,
        ]);
    }

    /**
     * Normalise le nom du centre
     * @param string|null $centerName
     * @return string
     */
    private function normalizeCenterName(?string $centerName): string
    {
        if ($centerName === null) {
            return 'NON SPÉCIFIÉ';
        }
        // Remove dots, trim spaces, and convert to uppercase
        return trim(str_replace('.', '', strtoupper($centerName)));
    }

    #[Route('/generate-all-candidates-pdf', name: 'app_generate_all_candidates_pdf')]
    public function generateAllCandidatesPdf(EntityManagerInterface $entityManager): Response
    {
        $candidates = $entityManager->getRepository(Candidate::class)
            ->findBy([], ['name' => 'ASC']);

        $zipFileName = 'all_candidates_lists.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        // Generate overall list
        $overallPdfContent = $this->generatePdf('LISTE GÉNÉRALE DES CANDIDATS', $candidates);
        $zip->addFromString('LISTE GENERALE DES CANDIDATS.pdf', $overallPdfContent);

        // Define country aliases
        $countryAliases = [
            'CAMEROON' => 'CAMEROUN',
            // Add other aliases if needed
        ];

        // Generate lists by country
        $candidatesByCountry = [];
        foreach ($candidates as $candidate) {
            $nationality = $candidate->getNationality() ?? 'NATIONALITÉ INCONNUE';
            $nationality = strtoupper($nationality);

            // Check if the nationality has an alias
            if (isset($countryAliases[$nationality])) {
                $nationality = $countryAliases[$nationality];
            }

            $candidatesByCountry[$nationality][] = $candidate;
        }

        foreach ($candidatesByCountry as $country => $countryCandidates) {
            $countryPdfContent = $this->generatePdf("LISTE DES CANDIDATS: $country", $countryCandidates, $country);
            $zip->addFromString("LISTE DES CANDIDATS - " . $country . ".pdf", $countryPdfContent);
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        // Delete the temporary file
        unlink($zipFilePath);

        return $response;
    }
    private function generatePdf(string $title, array $candidates, ?string $nationality = null): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $html = $this->renderView('candidate/pdf_listAll.html.twig', [
            'title' => $title,
            'candidates' => $candidates,
            'nationality' => $nationality
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }


    #[Route('/generate-candidates-per-center-pdf', name: 'app_generate_candidates_per_center_pdf')]
    public function generateCandidatesPerCenterPdf(EntityManagerInterface $entityManager): Response
    {
        $centers = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('c.examinationCenter')
            ->distinct()
            ->getQuery()
            ->getResult();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $zipFileName = 'center_candidates.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        foreach ($centers as $center) {
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['examinationCenter' => $center['examinationCenter']], ['name' => 'ASC']);

            $html = $this->renderView('candidate/pdf_listCenter.html.twig', [
                'title' => $center['examinationCenter'] . ' Candidates',
                'candidates' => $candidates,
                'center' => $center['examinationCenter']
            ]);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $pdfContent = $dompdf->output();
            $pdfFileName = $center['examinationCenter'] . '_candidates.pdf';

            $zip->addFromString($pdfFileName, $pdfContent);
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        // Delete the temporary file
        unlink($zipFilePath);

        return $response;
    }

    #[Route('/generate-candidates-per-admissionType-pdf', name: 'app_generate_candidates_per_admissionType_pdf')]
    public function generateCandidatesPerAdmissionTypePdf(EntityManagerInterface $entityManager): Response
    {
        $studentAdmissionTypes = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('c.admissionType')
            ->distinct()
            ->getQuery()
            ->getResult();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $zipFileName = 'admission_type.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        foreach ($studentAdmissionTypes as $studentAdmissionType) {
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['admissionType' => $studentAdmissionType['admissionType']], ['name' => 'ASC']);

            $html = $this->renderView('candidate/pdf_listAdmissionType.html.twig', [
                'title' => $studentAdmissionType['admissionType'] . ' Candidates',
                'candidates' => $candidates,
                'admissionType' => $studentAdmissionType['admissionType']
            ]);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $pdfContent = $dompdf->output();
            $pdfFileName = $studentAdmissionType['admissionType'] . '_candidates.pdf';

            $zip->addFromString($pdfFileName, $pdfContent);
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        // Delete the temporary file
        unlink($zipFilePath);

        return $response;
    }

    #[Route('/generate-candidates-per-field-pdf', name: 'app_generate_candidates_per_field_pdf')]
    public function generateCandidatesPerFieldPdf(EntityManagerInterface $entityManager): Response
    {
        $centers = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('c.examinationCenter')
            ->distinct()
            ->getQuery()
            ->getResult();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $zipFileName = 'center_candidates.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        foreach ($centers as $center) {
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['examinationCenter' => $center['examinationCenter']], ['name' => 'ASC']);

            // Group candidates by field
            $candidatesByField = [];
            foreach ($candidates as $candidate) {
                $field = $candidate->getField() ?? 'Undefined';
                $candidatesByField[$field][] = $candidate;
            }

            foreach ($candidatesByField as $field => $fieldCandidates) {
                // Generate PDF for all candidates in the field
                $html = $this->renderView('candidate/pdf_list.html.twig', [
                    'title' => $center['examinationCenter'] . ' Candidates - ' . $field,
                    'candidates' => $fieldCandidates,
                    'field' => $field
                ]);

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();

                $pdfContent = $dompdf->output();
                $pdfFileName = $center['examinationCenter'] . '_' . $field . '_candidates.pdf';

                $zip->addFromString($pdfFileName, $pdfContent);

                // Generate PDFs for candidates per room (40 candidates per room)
                $roomNumber = 1;
                $candidatesInRoom = [];
                foreach ($fieldCandidates as $index => $candidate) {
                    $candidatesInRoom[] = $candidate;
                    if (count($candidatesInRoom) == 40 || $index == count($fieldCandidates) - 1) {
                        $html = $this->renderView('candidate/pdf_room_list.html.twig', [
                            'title' => $center['examinationCenter'] . ' - ' . $field . ' - Room ' . $roomNumber,
                            'candidates' => $candidatesInRoom,
                            'field' => $field,
                            'roomNumber' => $roomNumber
                        ]);

                        $dompdf = new Dompdf($options);
                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render();

                        $pdfContent = $dompdf->output();
                        $pdfFileName = $center['examinationCenter'] . '_' . $field . '_room' . $roomNumber . '.pdf';

                        $zip->addFromString($pdfFileName, $pdfContent);

                        $roomNumber++;
                        $candidatesInRoom = [];
                    }
                }
            }
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        // Delete the temporary file
        unlink($zipFilePath);

        return $response;
    }




    #[Route('/generate-candidates-per-center-excel', name: 'app_generate_candidates_per_center_excel')]
    public function generateCandidatesPerCenterExcel(EntityManagerInterface $entityManager): Response
    {
        $centers = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('c.examinationCenter')
            ->distinct()
            ->getQuery()
            ->getResult();

        $zipFileName = 'center_candidates_excel.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        foreach ($centers as $center) {
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['examinationCenter' => $center['examinationCenter']], ['name' => 'ASC']);

            $title = $center['examinationCenter'] . ' Candidates';
            $excelContent = $this->generateExcel($title, $candidates);

            $excelFileName = $center['examinationCenter'] . '_candidates.xlsx';
            $zip->addFromString($excelFileName, $excelContent);
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        unlink($zipFilePath);

        return $response;
    }



    #[Route('/generate-candidates-per-admissionType-excel', name: 'app_generate_candidates_per_admissionType_excel')]
    public function generateCandidatesPerAdmissionTypeExcel(EntityManagerInterface $entityManager): Response
    {
        $studentAdmissionTypes = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('c.admissionType')
            ->distinct()
            ->getQuery()
            ->getResult();

        $zipFileName = 'admission_type.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        foreach ($studentAdmissionTypes as $studentAdmissionType) {
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['admissionType' => $studentAdmissionType['admissionType']], ['name' => 'ASC']);

            // Générer un fichier Excel pour chaque type d'admission
            $excelContent = $this->generateExcel($studentAdmissionType['admissionType'] . ' Candidates', $candidates);
            $excelFileName = $studentAdmissionType['admissionType'] . '_candidates.xlsx';
            $zip->addFromString($excelFileName, $excelContent);
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        unlink($zipFilePath);

        return $response;
    }



    #[Route('/generate-candidates-per-field-excel', name: 'app_generate_candidates_per_field_excel')]
    public function generateCandidatesPerFieldExcel(EntityManagerInterface $entityManager): Response
    {
        $centers = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('c.examinationCenter')
            ->distinct()
            ->getQuery()
            ->getResult();

        $zipFileName = 'center_candidates.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        foreach ($centers as $center) {
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['examinationCenter' => $center['examinationCenter']], ['name' => 'ASC']);

            // Grouper les candidats par filière
            $candidatesByField = [];
            foreach ($candidates as $candidate) {
                $field = $candidate->getField() ?? 'Undefined';
                $candidatesByField[$field][] = $candidate;
            }

            foreach ($candidatesByField as $field => $fieldCandidates) {
                // Générer un fichier Excel pour tous les candidats de la filière
                $excelContent = $this->generateExcel($center['examinationCenter'] . ' Candidates - ' . $field, $fieldCandidates);
                $excelFileName = $center['examinationCenter'] . '_' . $field . '_candidates.xlsx';
                $zip->addFromString($excelFileName, $excelContent);

                // Générer des fichiers Excel pour les candidats par salle (40 candidats par salle)
                $roomNumber = 1;
                $candidatesInRoom = [];
                foreach ($fieldCandidates as $index => $candidate) {
                    $candidatesInRoom[] = $candidate;
                    if (count($candidatesInRoom) == 40 || $index == count($fieldCandidates) - 1) {
                        $excelContent = $this->generateExcel(
                            $center['examinationCenter'] . ' - ' . $field . ' - Room ' . $roomNumber,
                            $candidatesInRoom
                        );
                        $excelFileName = $center['examinationCenter'] . '_' . $field . '_room' . $roomNumber . '.xlsx';
                        $zip->addFromString($excelFileName, $excelContent);

                        $roomNumber++;
                        $candidatesInRoom = [];
                    }
                }
            }
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        unlink($zipFilePath);

        return $response;
    }




    #[Route('/generate-all-candidates-excel', name: 'app_generate_all_candidates_excel')]
    public function generateAllCandidatesExcel(EntityManagerInterface $entityManager): Response
    {
        $candidates = $entityManager->getRepository(Candidate::class)
            ->findBy([], ['name' => 'ASC']);

        $zipFileName = 'all_candidates_lists.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \RuntimeException('Cannot create zip file');
        }

        // Générer la liste générale
        $overallExcelContent = $this->generateExcel('LISTE GÉNÉRALE DES CANDIDATS', $candidates);
        $zip->addFromString('LISTE GENERALE DES CANDIDATS.xlsx', $overallExcelContent);

        // Alias des pays
        $countryAliases = [
            'CAMEROON' => 'CAMEROUN',
            // Ajouter d'autres alias si nécessaire
        ];

        // Générer les listes par pays
        $candidatesByCountry = [];
        foreach ($candidates as $candidate) {
            $nationality = $candidate->getNationality() ?? 'NATIONALITÉ INCONNUE';
            $nationality = strtoupper($nationality);

            if (isset($countryAliases[$nationality])) {
                $nationality = $countryAliases[$nationality];
            }

            $candidatesByCountry[$nationality][] = $candidate;
        }

        foreach ($candidatesByCountry as $country => $countryCandidates) {
            $countryExcelContent = $this->generateExcel("LISTE DES CANDIDATS: $country", $countryCandidates, $country);
            $zip->addFromString("LISTE DES CANDIDATS - " . $country . ".xlsx", $countryExcelContent);
        }

        $zip->close();

        $response = new Response(file_get_contents($zipFilePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipFileName . '"');
        $response->headers->set('Content-Length', filesize($zipFilePath));

        unlink($zipFilePath);

        return $response;
    }

    private function generateExcel(string $title, array $candidates, ?string $nationality = null): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:Z1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Column Headers
        $headers = [
            'ID',
            'Nom',
            'Sexe',
            'Région',
            'Département',
            'CNI',
            'Domaine',
            'Centre d\'examen',
            'Certificat',
            'Date de naissance',
            'Lieu de naissance',
            'Année BAC',
            'Année GCE',
            'Langue',
            'Date d\'inscription',
            'Numéro de transaction',
            'Numéro de téléphone',
            'Email',
            'Nationalité',
            'Photo',
            'Date d\'émission CNI',
            'Début secondaire',
            'Fin secondaire',
            'Système éducatif',
            'Type étude cycle 2',
            'Pays Baccalauréat',
            'Série BAC',
            'Mention BAC',
            'Pays A-Level',
            'Papiers A-Level',
            'Notes A-Level',
            'Année O-Level',
            'Papiers O-Level',
            'Opérateur de paiement',
            'Année probatoire',
            'ID Candidat',
            'Certificat médical',
            'Casier judiciaire',
            'Relevé de notes',
            'Certificat de naissance',
            'Vérification système éducatif',
            'Sujet BAC',
            'Sujet Probatoire',
            'Sujet A-Level',
            'Sujet O-Level',
            'Note sujet BAC',
            'Note sujet Probatoire',
            'Note sujet A-Level',
            'Note sujet O-Level',
            'Type d\'admission'
        ];

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '3', $header);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }
        $sheet->getStyle('A3:' . $column . '3')->getFont()->setBold(true);

        // Fill Candidate Data
        $row = 4;
        foreach ($candidates as $candidate) {
            $sheet->setCellValue('A' . $row, $candidate->getId());
            $sheet->setCellValue('B' . $row, $candidate->getName());
            $sheet->setCellValue('C' . $row, $candidate->getSexe());
            $sheet->setCellValue('D' . $row, $candidate->getRegion());
            $sheet->setCellValue('E' . $row, $candidate->getDepertement()); // corrected typo
            $sheet->setCellValue('F' . $row, $candidate->getCni());
            $sheet->setCellValue('G' . $row, $candidate->getField());
            $sheet->setCellValue('H' . $row, $candidate->getExaminationCenter());
            $sheet->setCellValue('I' . $row, $candidate->getCertificate());
            $sheet->setCellValue('J' . $row, $candidate->getDateOfBirth()?->format('Y-m-d'));
            $sheet->setCellValue('K' . $row, $candidate->getPlaceOfBirth());
            $sheet->setCellValue('L' . $row, $candidate->getCertificateYearBAC());
            $sheet->setCellValue('M' . $row, $candidate->getCertificateYearGCE());
            $sheet->setCellValue('N' . $row, $candidate->getLanguage());
            $sheet->setCellValue('O' . $row, $candidate->getRegistrationDate()?->format('Y-m-d'));
            $sheet->setCellValue('P' . $row, $candidate->getTransactionNumber());
            $sheet->setCellValue('Q' . $row, $candidate->getPhoneNumber());
            $sheet->setCellValue('R' . $row, $candidate->getEmail());
            $sheet->setCellValue('S' . $row, $candidate->getNationality());
            $sheet->setCellValue('T' . $row, $candidate->getPhoto());
            $sheet->setCellValue('U' . $row, $candidate->getCniIssueDate()?->format('Y-m-d'));
            $sheet->setCellValue('V' . $row, $candidate->getSecondaryEducationStartYear());
            $sheet->setCellValue('W' . $row, $candidate->getSecondaryEducationEndYear());
            $sheet->setCellValue('X' . $row, $candidate->getEducationSubsystem());
            $sheet->setCellValue('Y' . $row, $candidate->getSecondCycleStudyType());
            $sheet->setCellValue('Z' . $row, $candidate->getBaccalaureateCountry());
            $sheet->setCellValue('AA' . $row, $candidate->getBaccalaureateSeries());
            $sheet->setCellValue('AB' . $row, $candidate->getBaccalaureateMention());
            $sheet->setCellValue('AC' . $row, $candidate->getGceALevelCountry());
            $sheet->setCellValue('AD' . $row, $candidate->getGceALevelPapersCount());
            $sheet->setCellValue('AE' . $row, $candidate->getGceALevelGradeACount());
            $sheet->setCellValue('AF' . $row, $candidate->getGceOLevelYear());
            $sheet->setCellValue('AG' . $row, $candidate->getGceOLevelPapersCount());
            $sheet->setCellValue('AH' . $row, $candidate->getPaymentOperator());
            $sheet->setCellValue('AI' . $row, $candidate->getProbatoireYear());
            $sheet->setCellValue('AJ' . $row, $candidate->getCandidateID());
            $sheet->setCellValue('AK' . $row, $candidate->getMedicalCertificate());
            $sheet->setCellValue('AL' . $row, $candidate->getCriminalRecordExtract());
            $sheet->setCellValue('AM' . $row, $candidate->getTranscript());
            $sheet->setCellValue('AN' . $row, $candidate->getBirthCertificate());
            $sheet->setCellValue('AO' . $row, $candidate->getEducationalSystemCheck());
            $sheet->setCellValue('AP' . $row, $candidate->getBacSubject());
            $row++;
        }

        // Save to temporary file and return content
        $writer = new Xlsx($spreadsheet);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFilePath);

        $excelContent = file_get_contents($tempFilePath);
        unlink($tempFilePath);

        return $excelContent;
    }
}
