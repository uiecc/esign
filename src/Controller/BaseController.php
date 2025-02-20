<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BaseController extends AbstractController
{
    protected function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->isGranted($attributes, $subject)) {
            $exception = new AccessDeniedException($message);
            $exception->setAttributes($attributes);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        // Check if user is logged in and accessing a protected route
        if ($this->getUser() && !$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_logout');
        }

        return parent::render($view, $parameters, $response);
    }
}
