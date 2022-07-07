<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Exception\AppException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = match (true) {
            $exception instanceof AppException
                => new JsonResponse(['message' => $exception->getMessage()], $exception->getCode()),

            $exception instanceof AccessDeniedException
                => new JsonResponse(['message' => 'Доступ запрещен'], Response::HTTP_FORBIDDEN),

            default
                => new JsonResponse(['message' => 'Произошла неизвестная ошибка'], Response::HTTP_INTERNAL_SERVER_ERROR)
        };

        $event->setResponse($response);
    }
}