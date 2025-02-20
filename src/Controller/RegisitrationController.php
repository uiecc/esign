<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisitrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\EventListener\RegistrationFormListener;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


class RegisitrationController extends BaseController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,  UserRepository $userRepository): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute("app_candidate_register");
        }
        $user = new User();
        $form = $this->createForm(RegisitrationFormType::class, $user);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $existingUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cette adresse e-mail existe déjà.');
                return $this->render('registration/registration.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // $plainPassword = $form->get('plainPassword')->getData();
            // $confirmPassword = $form->get('confirmPassword')->getData();

            // if ($plainPassword !== $confirmPassword) {
            //     $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            //     return $this->render('registration/registration.html.twig', [
            //         'registrationForm' => $form->createView(),
            //     ]);
            // }
            
            // Encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Set default role to ROLE_USER
            $user->setRoles(['ROLE_USER']);

            $user->setHasRegistered(false);

            $entityManager->persist($user);
            $entityManager->flush();

            // You can add a success flash message here
            $this->addFlash('success', 'Votre compte a été créé avec succès.');
            
            $request->getSession()->set('register_success', true);
            return $this->redirectToRoute('app_register_status');
        }

        return $this->render('registration/registration.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route(path: '/register/status', name: 'app_register_status')]
    public function registerStatus(Request $request): Response
    {
        $session = $request->getSession();
        $success = $session->get('register_success', null);

        if ($success === null) {
            throw new AccessDeniedException('Direct access to this page is not allowed.');
        }

        $redirectUrl = $success ? $this->generateUrl('app_candidate_register') : $this->generateUrl('app_register');

        // Clear the session variable
        $session->remove('register_success');

        return $this->render('registration/register_status.html.twig', [
            'success' => $success,
            'redirect_url' => $redirectUrl
        ]);
    }
 }

