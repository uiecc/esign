<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


#[Route('/student')]
final class StudentController extends AbstractController
{
    #[Route(name: 'app_student_index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository): Response
    {
        return $this->render('student/index.html.twig', [
            'students' => $studentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($student);
                $entityManager->flush();

                $this->addFlash('success', 'L\'étudiant a été créé avec succès.');
                return $this->redirectToRoute('app_student_preview', ['id' => $student->getId()]);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cet email est déjà utilisé par un autre étudiant.');
            }
        }

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }


    #[Route('/preview/{id}', name: 'app_student_preview', methods: ['GET'])]
    public function preview(Student $student): Response
    {
        // Debug pour vérifier l'ID reçu

        return $this->render('student/preview.html.twig', [
            'student' => $student,
        ]);
    }


    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $this->addFlash('success', 'L\'étudiant a été modifié avec succès.');
                return $this->redirectToRoute('app_student_preview', ['id' => $student->getId()]);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cet email est déjà utilisé par un autre étudiant.');
            }
        }

        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_student_registration_pdf', methods: ['GET'])]
    public function generatePdf(Request $request, Student $student): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->renderView('student/pdf.html.twig', [
            'student' => $student,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Si un paramètre download est présent, force le téléchargement
        if ($request->query->has('download')) {
            return new Response(
                $dompdf->output(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="registration.pdf"'
                ]
            );
        }

        // Sinon, affiche le PDF dans l'iframe
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf'
            ]
        );
    }


    #[Route('/export-excel/{type}', name: 'app_student_export_excel')]
    public function exportExcel(string $type, StudentRepository $studentRepository): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes complets
        $headers = [
            'Matricule',
            'Nom complet',
            'Date de naissance',
            'Lieu de naissance',
            'Genre',
            'Nationalité',
            'Handicap',
            'Filière',
            'Baccalauréat',
            'Langue',
            'Email',
            'Téléphone',
            'Adresse',
            'Nom du père',
            'Téléphone du père',
            'Nom de la mère',
            'Téléphone de la mère',
            'Année académique'
        ];

        // Écrire les en-têtes
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
        }

        // Récupérer les données selon le type demandé
        switch ($type) {
            case 'alphabetic':
                $students = $studentRepository->findBy([], ['fullName' => 'ASC']);
                $filename = 'etudiants-ordre-alphabetique.xlsx';
                break;

            case 'country':
                $students = $studentRepository->findBy([], ['nationality' => 'ASC', 'fullName' => 'ASC']);
                $filename = 'etudiants-par-pays.xlsx';
                break;

            case 'filiere':
                $students = $studentRepository->findBy([], ['filiere' => 'ASC', 'fullName' => 'ASC']);
                $filename = 'etudiants-par-filiere.xlsx';
                break;

            case 'handicap':
                $students = $studentRepository->findBy(['handicap' => true], ['fullName' => 'ASC']);
                $filename = 'etudiants-avec-handicap.xlsx';
                break;

            default:
                throw new \InvalidArgumentException('Type d\'export invalide');
        }

        // Remplir les données
        $row = 2;
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row, $student->getMatricule());
            $sheet->setCellValue('B' . $row, $student->getFullName());
            $sheet->setCellValue('C' . $row, $student->getBirthDate() ? $student->getBirthDate()->format('d/m/Y') : '');
            $sheet->setCellValue('D' . $row, $student->getBirthPlace());
            $sheet->setCellValue('E' . $row, $student->getGender());
            $sheet->setCellValue('F' . $row, $student->getNationality());
            $sheet->setCellValue('G' . $row, $student->isHandicap() ? 'Oui' : 'Non'); // Changed here
            $sheet->setCellValue('H' . $row, $student->getFiliere());
            $sheet->setCellValue('I' . $row, $student->getBaccalaureat());
            $sheet->setCellValue('J' . $row, $student->getLanguage());
            $sheet->setCellValue('K' . $row, $student->getEmail());
            $sheet->setCellValue('L' . $row, $student->getPhone());
            $sheet->setCellValue('M' . $row, $student->getAddress());
            $sheet->setCellValue('N' . $row, $student->getParentName());
            $sheet->setCellValue('O' . $row, $student->getParentPhone());
            $sheet->setCellValue('P' . $row, $student->getMotherName());
            $sheet->setCellValue('Q' . $row, $student->getMotherPhone());
            $sheet->setCellValue('R' . $row, $student->getAcademicYear());
            $row++;
        }

        // Formattage automatique des colonnes
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Styliser les en-têtes
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD']
            ]
        ];
        $sheet->getStyle('A1:R1')->applyFromArray($headerStyle);

        // Ajouter des bordures aux cellules
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ]
        ];
        $sheet->getStyle('A1:R' . ($row - 1))->applyFromArray($borderStyle);

        // Créer le fichier Excel
        $writer = new Xlsx($spreadsheet);

        // Créer la réponse
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);

        return $this->file($temp_file, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
