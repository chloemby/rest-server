<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Exception\AppException;
use App\Service\Auth\Login\LoginService;
use App\Service\Auth\Registration\RegistrationService;
use App\Service\Auth\Registration\UserRegistrationData;
use App\Service\Auth\Verification\EmailVerificationService;
use App\UserRole;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route(path: '/api/auth')]
class AuthController extends AbstractController
{
    #[Parameter(name: 'username', description: 'Имя пользователя', required: true)]
    #[Parameter(name: 'password', description: 'Пароль пользователя', required: true)]
    #[Parameter(name: 'email', description: 'Электронная почта пользователя', required: true)]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_OK,
        description: 'Пользователь успешно зарегистрирован',
        content: new JsonContent(
            properties: [new Property(property: 'username', description: 'Имя пользователя', type: 'string')],
            type: 'object'
        )
    )]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Произошла ошибка в процессе регистрации',
        content: new JsonContent(properties: [new Property(property: 'message', type: 'string')], type: 'object'),
    )]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Произошла неизвестная ошибка',
        content: new JsonContent(properties: [new Property(property: 'message', type: 'string')], type: 'object')
    )]
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
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[RequestBody(
        description: 'Данные для входа',
        content: new JsonContent(
            properties: [
                new Property(property: 'username', type: 'string'),
                new Property(property: 'password', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new JsonContent(
            properties: [
                new Property(property: 'token', description: 'Bearer токен аутентификации', type: 'string')
            ],
            type: 'object'
        )
    )]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Произошла ошибка в процессе регистрации',
        content: new JsonContent(properties: [new Property(property: 'message', type: 'string')], type: 'object'),
    )]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Произошла неизвестная ошибка',
        content: new JsonContent(properties: [new Property(property: 'message', type: 'string')], type: 'object')
    )]
    #[Route(path: '/login', name: 'api-auth-login', methods: [Request::METHOD_POST])]
    public function loginAction(
        UserInterface $user,
        LoginService $service
    ): JsonResponse {
        try {
            return new JsonResponse(['token' => $service->login($user)]);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Неизвестная ошибка'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/verify', name: 'api-auth-verify-email', methods: [Request::METHOD_GET])]
    public function verifyAction(
        Request $request,
        EmailVerificationService $service,
        #[CurrentUser] ?User $user
    ): Response {
        try {
            if (!$user || !$user->hasRole(UserRole::USER)) {
                throw new AccessDeniedException();
            }

            try {
                $service->verify($user, $request->getUri());
            } catch (VerifyEmailExceptionInterface $exception) {
                return new Response('Произошла ошибка: ' . $exception->getReason());
            }

            return new Response('Success');
        } catch (AccessDeniedException) {
            return new JsonResponse(['message' => 'Доступ запрещен'], Response::HTTP_FORBIDDEN);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Произошла неизвестная ошибка'], Response::HTTP_INTERNAL_SERVER_ERROR);        }
    }
}