<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Debug\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends BaseController
{
    public function show(\Throwable $exception): Response
    {
        $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;

        if ($statusCode === 404) {
            return $this->render('error/error404.html.twig', [
                'status_code' => $statusCode,
                'status_text' => Response::$statusTexts[$statusCode] ?? 'Unknown Error',
            ], new Response('', $statusCode));
        }

        return $this->render('error/error.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Unknown Error',
        ], new Response('', $statusCode));
    }
}
