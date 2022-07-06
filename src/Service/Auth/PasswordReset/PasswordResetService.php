<?php

declare(strict_types=1);

namespace App\Service\Auth\PasswordReset;

use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class PasswordResetService
{
    private EventDispatcherInterface $dispatcher;
    private UserRepository $userRepository;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        UserRepository $userRepository,
        ResetPasswordHelperInterface $resetPasswordHelper,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->dispatcher = $dispatcher;
        $this->userRepository = $userRepository;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->validator = $validator;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * @throws NotFoundException
     */
    public function forgot(string $email): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            throw new NotFoundException('Пользователя с таким Email не существует');
        }

        $this->dispatcher->dispatch(new UserForgotPasswordEvent($user));
    }

    /**
     * @throws ValidationException
     * @throws ResetPasswordExceptionInterface
     */
    public function reset(
        string $resetToken,
        string $plainPassword,
        string $repeatPlainPassword
    ): void {
        $this->validatePassword($plainPassword, $repeatPlainPassword);

        /** @var User $user */
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($resetToken);

        $this->resetPasswordHelper->removeResetRequest($resetToken);

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

        $this->userRepository->save($user);
    }

    /**
     * @throws ValidationException
     */
    private function validatePassword(
        string $plainPassword,
        string $repeatPlainPassword
    ): void {
        if ($plainPassword !== $repeatPlainPassword) {
            throw new ValidationException('Введенные пароли не совпадают');
        }

        $violations = $this->validator->validatePropertyValue(
            User::class,
            'password',
            $plainPassword
        );

        if ($violations->count() > 0) {
            throw new ValidationException($violations->get(0)->getMessage());
        }
    }
}