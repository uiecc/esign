<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends BaseController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        if ($this->getUser()) {
            $candidate = $this->getUser()->getCandidate();
            if ($this->getUser()->hasRegistered()) {
                return $this->redirectToRoute('app_candidate_preview', [
                    'id' => $candidate->getId(),
                    'examinationLanguage' => $candidate->getLanguage()
                ]);
            }
            return $this->redirectToRoute('app_login_status', ['success' => true]);
        }

        if ($request->isXmlHttpRequest()) {
            try {
                $email = $request->request->get('email');
                $password = $request->request->get('password');

                $errors = [];
                if (empty($email)) {
                    $errors['email'] = $translator->trans('login.error.email_required', [], 'security');
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = $translator->trans('login.error.invalid_email', [], 'security');
                }

                if (empty($password)) {
                    $errors['password'] = $translator->trans('login.error.password_required', [], 'security');
                }

                if (!empty($errors)) {
                    return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
                }

                // Assuming authentication failure is handled elsewhere, return success response for now
                return new JsonResponse(['success' => true]);

            } catch (\Exception $e) {
                return new JsonResponse([
                    'error' => $translator->trans('login.error.unknown', [], 'security')
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Handle non-AJAX requests
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/login/status', name: 'app_login_status')]
    public function loginStatus(Request $request): Response
    {
        $session = $request->getSession();
        $success = $session->get('login_success', null);

        if ($success === null) {
            // If login_success is not set, it means the user is already logged in
            if ($this->getUser()) {
                $success = true;
            } else {
                throw new AccessDeniedException('Direct access to this page is not allowed.');
            }
        }

        $redirectUrl = $success ? $this->generateUrl('app_candidate_register') : $this->generateUrl('app_login');

        // Clear the session variable
        $session->remove('login_success');

        return $this->render('security/login_status.html.twig', [
            'success' => $success,
            'redirect_url' => $redirectUrl
        ]);
    }
}
