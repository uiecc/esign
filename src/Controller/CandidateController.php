<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\User;
use App\Form\CandidateFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Encoding\Encoding;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZipArchive;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use LDAP\Result;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;




class CandidateController extends BaseController
{
    private $requestStack;

    public function __construct(private Security $security, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    #[Route('/candidate/register', name: 'app_candidate_register')]
    public function registerOrUpdate(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        LoggerInterface $logger,
        CsrfTokenManagerInterface $csrfTokenManager,
        TranslatorInterface $translator
    ): Response {

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Fetch the candidate data
        $candidate = $user->getCandidate();
        $isNewCandidate = false;

        if ($candidate && $user->hasRegistered()) {
            // If the user is trying to register but has already registered
            // Check if they are allowed to update their information
            $now = new \DateTime();
            $registrationDate = $candidate->getRegistrationDate();
            $diff = $now->diff($registrationDate);

            // Only redirect if the user came from the update button or the 7-day window has passed
            if ($request->query->get('update') !== 'true' && $diff->days < 7) {
                return $this->redirectToRoute('app_candidate_preview', [
                    'id' => $candidate->getId(),
                    'examinationLanguage' => $candidate->getLanguage(),
                    'allowUpdate' => true,
                ]);
            }
        } else {
            // New candidate case
            if (!$candidate) {
                $candidate = new Candidate();
                $candidate->setUser($user);
                $candidate->generateCandidateID($entityManager);
                $candidate->setRegistrationDate(new \DateTime());
                $isNewCandidate = true;
            }
        }

        $form = $this->createForm(CandidateFormType::class, $candidate);

        // Load data from cookie if it exists
        $session = $this->getSession();
        $savedData = $session->get('candidateFormData_' . $user->getId());
        if ($savedData) {
            $form->submit($savedData, false);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $candidate->setName(strtoupper($candidate->getName()));
                    $candidate->setEmail($user->getEmail());
                    $this->handleFileUpload($form, $candidate, $slugger, 'certificate', 'certificates_directory');
                    $this->handleFileUpload($form, $candidate, $slugger, 'photo', 'photos_directory');
                    $this->handleFileUpload($form, $candidate, $slugger, 'birthCertificate', 'birthCertificate_directory');

                    $this->handleFileUpload($form, $candidate, $slugger, 'medicalCertificate', 'medical_certificates_directory');
                    $this->handleFileUpload($form, $candidate, $slugger, 'criminalRecordExtract', 'criminal_records_directory');
                    $this->handleFileUpload($form, $candidate, $slugger, 'transcript', 'transcripts_directory');


                    $user->setHasRegistered(true);

                    if ($isNewCandidate) {
                        $entityManager->persist($candidate);
                    }


                    $entityManager->flush();

                    $examinationLanguage = $form->get('language')->getData();

                    // dump($examinationLanguage);


                    $csrfTokenManager->removeToken('csrf_token_id');

                    // // Clear the cookie after successful submission
                    $session->remove('candidateFormData_' . $user->getId());

                    $response = $this->redirectToRoute('app_candidate_confirm', [
                        'id' => $candidate->getId(),
                        'examinationLanguage' => $form->get('language')->getData(),
                    ]);
                    // $response->headers->clearCookie('candidateFormData');

                    //$this->addFlash('success', 'Vos informations ont été enregistrées avec succès.');
                    return $response;
                } catch (\Exception $e) {
                    $logger->error('Error saving candidate: ' . $e->getMessage());
                    $this->addFlash('error', $e);
                }
            } else {
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $logger->error('Form validation error: ' . $error->getMessage());
                    $this->addFlash('error', $error->getMessage());
                }
                // Clear the CSRF token on error
                $csrfTokenManager->removeToken('csrf_token_id');
            }
        }

        // If the form is not submitted via POST, or if there were errors, render the form
        return $this->render('candidate/register_update.html.twig', [
            'form' => $form->createView(),
            'candidate' => $candidate,
        ]);
    }

    private function handleFileUpload($form, $candidate, $slugger, $fieldName, $targetDirectory)
    {
        $file = $form[$fieldName]->getData();
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($candidate->getName());
            $fileExtension = $file->guessExtension();

            $allowedExtensions = ['pdf', 'jpeg', 'jpg', 'png'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new \Exception('Type de fichier non autorisé. Veuillez télécharger un PDF ou une image (JPEG, JPG, PNG).');
            }

            // Determine the new filename based on the field type
            switch ($fieldName) {
                case 'certificate':
                    $newFilename = $safeFilename . '-' . $candidate->getCandidateID() . '-' . 'Diplome' . '.' . $fileExtension;
                    break;
                case 'photo':
                    $newFilename = $safeFilename . '-' . $candidate->getCandidateID() . '-' . 'Photo' . '.' . $fileExtension;
                    break;
                case 'birthCertificate':
                    $newFilename = $safeFilename . '-' . $candidate->getCandidateID() . '-' . 'ActeNaissance' . '.' . $fileExtension;
                    break;
                default:
                    $newFilename = $safeFilename . '-' . $candidate->getCandidateID() . '.' . $fileExtension;
            }


            // Remove any special characters from the filename
            $newFilename = preg_replace('/[^A-Za-z0-9\-.]/', '', $newFilename);

            $targetPath = $this->getParameter($targetDirectory) . '/' . $newFilename;

            try {
                $filesystem = new Filesystem();

                // Remove the old file if it exists
                if ($filesystem->exists($targetPath)) {
                    $filesystem->remove($targetPath);
                }

                // Move the new file
                $file->move(
                    $this->getParameter($targetDirectory),
                    $newFilename
                );

                // Update the candidate entity with the new filename
                $setterMethod = 'set' . ucfirst($fieldName);
                $candidate->$setterMethod($newFilename);

                if ($fileExtension === 'pdf') {
                    // Add specific processing for PDFs if necessary
                }
            } catch (FileException $e) {
                $this->logger->error('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
                throw $e;
            }
        }
    }


    #[Route('/candidate/update/{id}', name: 'app_candidate_update')]
    public function update(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, int $id): Response
    {
        $candidate = $entityManager->getRepository(Candidate::class)->find($id);

        if (!$candidate) {
            throw $this->createNotFoundException('Candidate not found');
        }

        if (!$this->canEditCandidate($candidate)) {
            $this->addFlash('error', 'You can no longer edit your information. The 7-day edit window has expired.');
            return $this->redirectToRoute('app_candidate_preview', ['id' => $candidate->getId()]);
        }

        $form = $this->createForm(CandidateFormType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUpload($form, $candidate, $slugger, 'certificate', 'certificates_directory');
            $this->handleFileUpload($form, $candidate, $slugger, 'photo', 'photos_directory');
            $this->handleFileUpload($form, $candidate, $slugger, 'birthCertificate', 'birthCertificate_directory');

            $entityManager->flush();

            $this->addFlash('success', 'Your information has been updated successfully.');
            return $this->redirectToRoute('app_candidate_preview', ['id' => $candidate->getId()]);
        }

        return $this->render('candidate/update.html.twig', [
            'form' => $form->createView(),
            'candidate' => $candidate,
        ]);
    }


    private function canEditCandidate(Candidate $candidate): bool
    {
        $now = new \DateTime();
        $diff = $now->diff($candidate->getRegistrationDate());
        return $diff->days < 7;
    }



    #[Route('/candidate/preview/{id}/{examinationLanguage?french}', name: 'app_candidate_preview')]
    public function preview(EntityManagerInterface $entityManager, int $id, string $examinationLanguage): Response
    {
        $candidate = $entityManager->getRepository(Candidate::class)->find($id);
        if (!$candidate) {
            throw $this->createNotFoundException('Candidate not found.');
        }

        $qrCode = $this->generateQrCode($candidate, $examinationLanguage);

        // Get the current user
        $user = $this->getUser();

        $allowUpdate = false;
        $updateUrl = null;

        if ($user && $user->hasRegistered() && $user->getCandidate() === $candidate) {
            $now = new \DateTime();
            $registrationDate = $candidate->getRegistrationDate();
            $diff = $now->diff($registrationDate);

            if ($diff->days < 7) {
                $allowUpdate = true;
                $updateUrl = $this->generateUrl('app_candidate_register', ['update' => 'true']);
            }
        }

        return $this->render('candidate/preview.html.twig', [
            'candidate' => $candidate,
            'examinationLanguage' => $examinationLanguage,
            'qrCodeBase64' => $qrCode,
            'allowUpdate' => $allowUpdate,
            'updateUrl' => $updateUrl,
        ]);
    }




    #[Route('/candidate/generate-pdf/{id}/{examinationLanguage?french}', name: 'app_candidate_generate_pdf')]
    public function generatePdf(
        EntityManagerInterface $entityManager,
        int $id,
        Security $security,
        Request $request,
        string $examinationLanguage,
        MailerInterface $mailer
    ): Response {
        $candidate = $entityManager->getRepository(Candidate::class)->find($id);
        if (!$candidate) {
            $this->addFlash('error', 'Candidat non trouvé.');
            return $this->redirectToRoute('app_canditate_not_found');
        }
    
        // Generate QR code
        $qrCode = $this->generateQrCode($candidate, $examinationLanguage);
    
        $options = new Options();
        $options->set('defaultFont', 'Montserrat');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
    
        $html = $this->renderView('candidate/pdf.html.twig', [
            'candidate' => $candidate,
            'examinationLanguage' => $examinationLanguage,
            'qrCodeBase64' => $qrCode,
        ]);
    
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
    
        // Create a temporary file to store the PDF
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempFile, $pdfContent);
    
        // Prepare email content
        $emailHtml = $this->renderView('candidate/registration_form_email.html.twig', [
            'candidate' => $candidate,
            'examinationLanguage' => $examinationLanguage,
        ]);
    
        // Send email with PDF attachment
        try {
            $email = (new Email())
                ->from('uiecc@esign.cm')
                ->to($candidate->getEmail())
                ->subject('Your Registration Form / Votre Formulaire d\'Inscription')
                ->html($emailHtml)
                ->text(strip_tags($emailHtml))
                ->addPart(new DataPart(new File($tempFile), 'registration_form.pdf', 'application/pdf'));
    
            $mailer->send($email);
            $this->addFlash('success', 'The PDF has been sent to your email. / Le PDF a été envoyé à votre email.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send email: ' . $e->getMessage());
        }
    
        // Set a flag in the session to indicate that the PDF has been generated
        $this->getSession()->set('pdf_generated', true);
    
        // Force the session to be saved immediately
        $this->getSession()->save();
    
        // Logout the user
        $security->logout(false);
    
        // Prepare the response for automatic download
        $response = new BinaryFileResponse($tempFile);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'fiche_inscription.pdf'
        );
        $response->deleteFileAfterSend(true);
    
        // Set headers to prevent caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
    
        return $response;
    }

    #[Route('/candidate/end-registration', name: 'app_end_registration')]
    public function endregistration(): Response{
        return $this->render('candidate/end_registration.html.twig');
    }
    
    // public function generatePdf(
    //     EntityManagerInterface $entityManager,
    //     int $id,
    //     Security $security,
    //     Request $request,
    //     string $examinationLanguage,
    //     MailerInterface $mailer
    // ): Response {
    //     $candidate = $entityManager->getRepository(Candidate::class)->find($id);
    //     if (!$candidate) {
    //         $this->addFlash('error', 'Candidat non trouvé.');
    //         return $this->redirectToRoute('app_canditate_not_found');
    //     }

    //     // Generate QR code
    //     $qrCode = $this->generateQrCode($candidate, $examinationLanguage);

    //     $options = new Options();
    //     $options->set('defaultFont', 'Montserrat');
    //     $options->set('isRemoteEnabled', true);
    //     $dompdf = new Dompdf($options);

    //     $html = $this->renderView('candidate/pdf.html.twig', [
    //         'candidate' => $candidate,
    //         'examinationLanguage' => $examinationLanguage,
    //         'qrCodeBase64' => $qrCode,
    //     ]);

    //     $dompdf->loadHtml($html);
    //     $dompdf->setPaper('A4', 'portrait');
    //     $dompdf->render();
    //     $pdfContent = $dompdf->output();

    //     // Create a temporary file to store the PDF
    //     $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
    //     file_put_contents($tempFile, $pdfContent);

    //     // Prepare email content
    //     $emailHtml = $this->renderView('candidate/registration_form_email.html.twig', [
    //         'candidate' => $candidate,
    //         'examinationLanguage' => $examinationLanguage,
    //     ]);

    //     $success = true;
    //     $message = 'The PDF has been sent to your email. / Le PDF a été envoyé à votre email.';


    //     // Send email with PDF attachment
    //     try {
    //         $email = (new Email())
    //             ->from('uiecc@esign.cm')
    //             ->to($candidate->getEmail())
    //             ->subject('Your Registration Form / Votre Formulaire d\'Inscription')
    //             ->html($emailHtml)
    //             ->text(strip_tags($emailHtml))
    //             ->addPart(new DataPart(new File($tempFile), 'registration_form.pdf', 'application/pdf'));

    //         $mailer->send($email);
    //         $this->addFlash('success', 'The PDF has been sent to your email. / Le PDF a été envoyé à votre email.');
    //     } catch (\Exception $e) {
    //         $this->addFlash('error', 'Failed to send email: ' . $e->getMessage());
    //         $success = false;
    //         $message = 'Failed to send email: ' . $e->getMessage();
    //     }


    //     // Set a flag in the session to indicate that the PDF has been generated
    //     $this->getSession()->set('pdf_generated', true);

    //     // Force the session to be saved immediately
    //     $this->getSession()->save();

    //     // Logout the user
    //     $security->logout(false);

    //     // Prepare the response for automatic download
    //     if ($success) {
    //         $response = new BinaryFileResponse($tempFile);
    //         $response->setContentDisposition(
    //             ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    //             'fiche_inscription.pdf'
    //         );
    //         $response->deleteFileAfterSend(true);

    //         // Set headers to prevent caching
    //         $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
    //         $response->headers->set('Pragma', 'no-cache');
    //         $response->headers->set('Expires', '0');

    //         return $response;
    //     } else {
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => $message
    //         ]);
    //     }
    // }

    private function generateQrCode(Candidate $candidate, string $examinationLanguage): string
    {
        // Create QR code data
        $qrData = [
            'Nom' => $candidate->getName(),
            'Centre d\'examen' => $candidate->getExaminationCenter(),
            'Langue de composition' => $examinationLanguage,
            "Domaine d'etudes" => $candidate->getField(),
            'Numero CNI' => $candidate->getCni(),
            'ID Unique' => $candidate->getCandidateID(),
        ];

        // 'authKey' => $this->generateAuthKey($candidate), maybe of possible future use
        // 'timestamp' => time(),  this too maybe

        // Ensure proper UTF-8 encoding
        $jsonData = json_encode($qrData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $qrCode = QrCode::create($jsonData)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return base64_encode($result->getString());
    }

    #[Route('/candidate/download-pdf', name: 'app_download_pdf')]
    public function downloadPdf(): Response
    {
        $tempFile = $this->getSession()->get('pdf_download_path');

        if (!$tempFile || !file_exists($tempFile)) {
            throw $this->createNotFoundException('The PDF file was not found.');
        }

        $response = new BinaryFileResponse($tempFile);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'fiche_inscription.pdf'
        );
        $response->deleteFileAfterSend(true);

        // Clear the session variables
        $this->getSession()->remove('pdf_download_path');
        $this->getSession()->remove('pdf_generated');

        return $response;
    }


    private function generateAuthKey(Candidate $candidate): string
    {
        // Generate a unique authentication key
        // This is a simple example; you might want to use a more sophisticated method
        return hash('sha256', $candidate->getId() . $candidate->getEmail() . time() . 'some_secret_key');
    }


    #[Route('/candidate/check-access', name: 'app_candidate_check_access')]
    public function checkAccess(): Response
    {
        if ($this->getSession()->get('pdf_downloaded', false)) {
            return new Response('Access denied. PDF has been downloaded.', Response::HTTP_FORBIDDEN);
        }
        return new Response('Access granted.');
    }


    #[Route('/candidate-not-found', name: 'app_canditate_not_found')]
    public function candidateNotFound(): Response
    {
        return $this->render('error/error.html.twig');
    }

    #[Route('/candidate/confirm/', name: 'app_candidate_confirm')]
    public function confirmSubmission(Request $request): Response
    {
        $user = $this->getUser();
        $session = $this->getSession();
        $candidate = $user->getCandidate();
        $session->remove('candidateFormData_' . $user->getId());
        $examinationLanguage = $request->query->get('examinationLanguage');
        //dump($examinationLanguage);
        return $this->render(
            'candidate/confirm.html.twig',
            [
                'candidateId' => $candidate->getId(),
                'examinationLanguage' => $examinationLanguage,
                // Other variables if needed
            ]
        );
    }

    #[Route('/candidate/save-progress', name: 'app_candidate_save_progress', methods: ['POST'])]
    public function saveProgress(Request $request, LoggerInterface $logger): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            $logger->error('Invalid JSON data received');
            return new JsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
        }

        try {
            $cookieName = 'candidateFormData_' . $user->getId();
            $encryptedData = $this->encryptData(json_encode($data), $user->getPassword());

            $response = new JsonResponse(['success' => true]);
            $cookie = new Cookie(
                $cookieName,
                $encryptedData,
                time() + (30 * 24 * 60 * 60), // 30 days
                '/',
                null,
                true, // secure
                true  // httpOnly
            );
            $response->headers->setCookie($cookie);

            return $response;
        } catch (\Exception $e) {
            $logger->error('Error saving progress: ' . $e->getMessage());
            return new JsonResponse(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()], 500);
        }
    }

    private function encryptData(string $data, string $key): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private function decryptData(string $data, string $key): string
    {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }


    #[Route('/candidate/post-download', name: 'app_candidate_post_download')]
    public function postDownload(): Response
    {
        $pdfDownloaded = $this->getSession()->get('pdf_downloaded', false);

        if ($pdfDownloaded) {
            // Clear the flag
            $this->getSession()->remove('pdf_downloaded');

            // Redirect to the preview page
            return $this->redirectToRoute('app_candidate_preview', ['id' => $this->getUser()->getCandidate()->getId()]);
        }

        // If PDF wasn't downloaded, redirect to login
        return $this->redirectToRoute('app_login');
    }


    #[Route('/candidate/generate-all-pdfs', name: 'app_candidate_generate_all_pdfs')]
    public function generateAllPdfs(EntityManagerInterface $entityManager): Response
    {
        // Récupère tous les centres d'examen uniques
        $centers = $entityManager->getRepository(Candidate::class)
            ->createQueryBuilder('c')
            ->select('DISTINCT c.examinationCenter')
            ->getQuery()
            ->getResult();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', $this->getParameter('kernel.project_dir') . '/public');

        $zipFilename = 'tous_les_centres.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFilename;
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($centers as $centerData) {
            $center = $centerData['examinationCenter'];

            // Récupère tous les candidats pour ce centre
            $candidates = $entityManager->getRepository(Candidate::class)
                ->findBy(['examinationCenter' => $center]);

            if (!empty($candidates)) {
                $centerZip = new ZipArchive();
                $centerZipPath = sys_get_temp_dir() . '/' . preg_replace('/[^a-zA-Z0-9]+/', '_', $center) . '.zip';
                $centerZip->open($centerZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                foreach ($candidates as $candidate) {
                    $dompdf = new Dompdf($options);
                    $html = $this->renderView('candidate/pdf_list.html.twig', [
                        'center' => $center,
                        'candidates' => $candidates,
                    ]);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'landscape');
                    $dompdf->render();

                    $pdfContent = $dompdf->output();
                    $pdfFilename = 'candidat_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $candidate->getName()) . '.pdf';

                    $centerZip->addFromString($pdfFilename, $pdfContent);
                }

                $centerZip->close();
                $zip->addFile($centerZipPath, preg_replace('/[^a-zA-Z0-9]+/', '_', $center) . '.zip');
            }
        }

        $zip->close();

        $response = new Response(file_get_contents($zipPath));
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $zipFilename
        );

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);

        // Supprime les fichiers temporaires
        unlink($zipPath);
        foreach ($centers as $centerData) {
            $centerZipPath = sys_get_temp_dir() . '/' . preg_replace('/[^a-zA-Z0-9]+/', '_', $centerData['examinationCenter']) . '.zip';
            if (file_exists($centerZipPath)) {
                unlink($centerZipPath);
            }
        }

        return $response;
    }


    #[Route('/candidate/load-progress', name: 'app_candidate_load_progress', methods: ['GET'])]
    public function loadProgress(Request $request, LoggerInterface $logger): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $cookieName = 'candidateFormData_' . $user->getId();
        $cookieData = $request->cookies->get($cookieName);

        if ($cookieData) {
            try {
                $decryptedData = $this->decryptData($cookieData, $user->getPassword());
                $savedData = json_decode($decryptedData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON: ' . json_last_error_msg());
                }
                return new JsonResponse(['success' => true, 'formData' => $savedData]);
            } catch (\Exception $e) {
                $logger->error('Error loading progress: ' . $e->getMessage());
                return new JsonResponse(['success' => false, 'message' => 'Error loading data: ' . $e->getMessage()], 500);
            }
        }

        return new JsonResponse(['success' => false, 'message' => 'No saved data found']);
    }


    #[Route('/end', name: 'app_end')]
public function endRegistrations(EntityManagerInterface $entityManager): Response
{

    return $this->render(
        'candidate/end_registration.html.twig',);
}
}
