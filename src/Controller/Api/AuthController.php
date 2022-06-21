<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\AppException;
use App\Service\Auth\Login\LoginService;
use App\Service\Auth\Registration\RegistrationService;
use App\Service\Auth\Registration\UserRegistrationData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(path: '/api/auth')]
class AuthController extends AbstractController
{
    #[Route(path: '/register', name: 'api-auth-register', methods: [Request::METHOD_POST])]
    public function registerAction(
        Request $request,
        RegistrationService $service
    ): JsonResponse {
        try {
            $userRegistrationData = new UserRegistrationData(
                (string)$request->get('username'),
                (string)$request->get('password'),
                (string)$request->get('email')
            );

            $user = $service->register($userRegistrationData);

            return new JsonResponse(['username' => $user->getUserIdentifier()]);
        } catch (AppException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => 'Неизвестная ошибка'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/login', name: 'api-auth-login', methods: [Request::METHOD_POST])]
    public function loginAction(
        UserInterface $user,
        LoginService $service
    ): JsonResponse {
        try {
            return new JsonResponse(['token' => $service->login($user)]);
        } catch (AppException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => 'Неизвестная ошибка'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}