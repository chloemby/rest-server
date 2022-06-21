<?php

declare(strict_types=1);

namespace App\Service\Auth\Login;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginService
{
    private JWTTokenManagerInterface $tokenManager;

    public function __construct(JWTTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function login(UserInterface $user): string
    {
        return $this->tokenManager->create($user);
    }
}