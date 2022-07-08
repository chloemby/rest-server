<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\Auth\Login\LoginService;
use App\Service\Auth\PasswordReset\PasswordResetService;
use App\Service\Auth\Registration\RegistrationService;
use App\Service\Auth\Registration\UserRegistrationData;
use App\Service\Auth\Verification\EmailVerificationService;
use App\UserRole;
use Nelmio\ApiDocBundle\Annotation\Security;
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
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route(path: '/api/auth')]
class AuthController extends AbstractController
{
    /**
     * @throws ValidationException
     */
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
        LoginService $loginService,
        RegistrationService $registrationService
    ): JsonResponse {
        $userRegistrationData = new UserRegistrationData(
            (string)$request->get('username'),
            (string)$request->get('password'),
            (string)$request->get('email')
        );

        $user = $registrationService->register($userRegistrationData);

        return new JsonResponse([
            'username' => $user->getUserIdentifier(),
            'token' => $loginService->login($user),
        ]);
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
        return new JsonResponse(['token' => $service->login($user)]);
    }

    #[Security(name: 'Bearer')]
    #[Route(path: '/verify', name: 'api-auth-verify-email', methods: [Request::METHOD_GET])]
    public function verifyAction(
        Request $request,
        EmailVerificationService $service,
        #[CurrentUser] ?User $user
    ): Response {
        if (!$user || !$user->hasRole(UserRole::USER)) {
            throw new AccessDeniedException();
        }

        try {
            $service->verify($user, $request->getUri());
        } catch (VerifyEmailExceptionInterface $exception) {
            return new Response('Произошла ошибка: ' . $exception->getReason());
        }

        return new Response('Success');
    }

    /**
     * @throws NotFoundException
     */
    #[Parameter(name: 'email', description: 'Электронная почта пользователя', required: true)]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new JsonContent(
            properties: [new Property(property: 'message', type: 'string')],
            type: 'object'
        )
    )]
    #[Route(path: '/forgot', name: 'api-v1-forgot-password', methods: [Request::METHOD_POST])]
    public function forgotPasswordAction(
        Request $request,
        PasswordResetService $service
    ): JsonResponse {
        $email = $request->get('email', '');

        $service->forgot($email);

        return new JsonResponse(['message' => 'На указанный адрес электронной почты отправлено письмо для']);
    }

    /**
     * @throws ValidationException
     * @throws ResetPasswordExceptionInterface
     */
    #[Parameter(
        name: 'token',
        description: 'Токен из ссылки, которая отправлена пользователю на почту (на форме ее можно получить из query-строки)',
        required: true
    )]
    #[Parameter(name: 'password', description: 'Новый пароль', required: true)]
    #[Parameter(name: 'repeat_password', description: 'Новый пароль еще раз', required: true)]
    #[\OpenApi\Attributes\Response(response: Response::HTTP_OK, description: 'OK')]
    #[\OpenApi\Attributes\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Произошла ошибка в процессе смены пароля',
        content: new JsonContent(
            properties: [new Property(property: 'message', type: 'string')], type: 'object'),
    )]
    #[Route(path: '/reset', name: 'api-v1-reset-password', methods: [Request::METHOD_POST])]
    public function resetPasswordAction(
        Request $request,
        PasswordResetService $service
    ): JsonResponse {
        $token = $request->get('token', '');
        $password = $request->get('password', '');
        $repeatPassword = $request->get('repeat_password', '');

        $service->reset($token, $password, $repeatPassword);

        return new JsonResponse();
    }
}