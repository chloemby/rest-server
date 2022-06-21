<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Exception\AppException;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(path: '/api/auth')]
class AuthController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;
    private JWTTokenManagerInterface $JWTTokenManager;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, JWTTokenManagerInterface $JWTTokenManager)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->JWTTokenManager = $JWTTokenManager;
    }

    #[Route(path: '/register', name: 'api-auth-register', methods: [Request::METHOD_POST])]
    public function registerAction(Request $request): JsonResponse
    {
        try {
            $username = (string)$request->get('username');
            $password = (string)$request->get('password');
            $email = (string)$request->get('email');

            $user = new User();
            $user->setUsername($username)
                ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
                ->setEmail($email);

            $this->userRepository->add($user, flush: true);

            return new JsonResponse(['username' => $user->getUserIdentifier()]);
        } catch (AppException $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        } catch (\Throwable) {
            return new JsonResponse('Неизвестная ошибка', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/login', name: 'api-auth-login', methods: [Request::METHOD_POST])]
    public function getUserTokenAction(UserInterface $user): JsonResponse
    {
        try {
            return new JsonResponse(['token' => $this->JWTTokenManager->create($user)]);
        } catch (\Throwable $e) {
            return new JsonResponse($e->getMessage());
        }
    }
}