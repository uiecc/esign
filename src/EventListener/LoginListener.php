<?php
// src/EventListener/LoginListener.php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginListener implements EventSubscriberInterface
{
    private $requestStack;
    private $validator;
    private $entityManager;
    private $translator;

    public function __construct(RequestStack $requestStack, ValidatorInterface $validator, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $email = $request->request->get('email');

        // Validate email format
        $emailConstraint = new Assert\Email();
        $errors = $this->validator->validate($email, $emailConstraint);

        if (count($errors) > 0) {
            $event->setResponse(new JsonResponse([
                'errors' => ['email' => $this->translator->trans('login.error.invalid_email')]
            ], 400));
            return;
        }

        // Check if email exists
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $event->setResponse(new JsonResponse([
                'errors' => ['email' => $this->translator->trans('login.error.email_not_exist')]
            ], 400));
            return;
        }

        // Handle other authentication failures
        $exception = $event->getException();
        if ($exception instanceof AuthenticationException) {
            $event->setResponse(new JsonResponse([
                'errors' => ['credentials' => $this->translator->trans('login.error.invalid_credentials')]
            ], 400));
        }
    }
}
