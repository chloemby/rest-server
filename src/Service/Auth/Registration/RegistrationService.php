<?php

declare(strict_types=1);

namespace App\Service\Auth\Registration;

use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        if ($this->isUserExist($userRegistrationInfo)) {
            throw new ValidationException(
                'Пользователь с таким именем/почтой уже существует',
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = new User();

        $user->setUsername($userRegistrationInfo->getUsername())
            ->setPassword($this->userPasswordHasher->hashPassword($user, $userRegistrationInfo->getPassword()))
            ->setEmail($userRegistrationInfo->getEmail());

        $this->validate($user);

        $this->userRepository->add($user, flush: true);

        return $user;
    }

    private function isUserExist(UserRegistrationData $userRegistrationInfo): bool
    {
        return $this->userRepository->findByUsernameOrEmail(
            $userRegistrationInfo->getUsername(),
            $userRegistrationInfo->getEmail()
        ) !== [];
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