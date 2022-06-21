<?php

declare(strict_types=1);

namespace App\Service\Auth\Registration;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function register(UserRegistrationData $userRegistrationInfo): User
    {
        $user = new User();

        $user->setUsername($userRegistrationInfo->getUsername())
            ->setPassword($this->userPasswordHasher->hashPassword($user, $userRegistrationInfo->getPassword()))
            ->setEmail($userRegistrationInfo->getEmail());

        $this->userRepository->add($user, flush: true);

        return $user;

    }
}