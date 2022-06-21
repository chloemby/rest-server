<?php

declare(strict_types=1);

namespace App\Service\Auth\Registration;

use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator
    ) {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function register(UserRegistrationData $userRegistrationInfo): User
    {
        $user = new User();

        $user->setUsername($userRegistrationInfo->getUsername())
            ->setPassword($this->userPasswordHasher->hashPassword($user, $userRegistrationInfo->getPassword()))
            ->setEmail($userRegistrationInfo->getEmail());

        $this->validate($user);

        $this->userRepository->add($user, flush: true);

        return $user;
    }

    /**
     * @throws ValidationException
     */
    private function validate(User $user): void
    {
        $violations = $this->validator->validate($user);

        if ($this->validator->validate($user)->count() > 0) {
            throw new ValidationException($violations->get(0)->getMessage());
        }
    }
}