<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Exception\NotFoundException;
use App\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws NotFoundException
     */
    public function getById(int $id): User
    {
        $user = $this->userRepository->findOneBy(['id' => $id, 'deletedAt' => null]);

        if ($user === null) {
            throw new NotFoundException('Пользователь не найдет');
        }

        return $user;
    }
}