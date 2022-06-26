<?php

declare(strict_types=1);

namespace App\Service\Auth\Verification;

use App\Entity\User;
use App\Repository\UserRepository;
use App\UserRole;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerificationService
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private UserRepository $userRepository;

    public function __construct(
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $userRepository
    ) {
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function verify(User $user, string $signature): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $signature,
            $user->getUserIdentifier(),
            $user->getEmail()
        );

        $user->setRoles([UserRole::VERIFIED_USER->value]);

        $this->userRepository->save($user);
    }
}