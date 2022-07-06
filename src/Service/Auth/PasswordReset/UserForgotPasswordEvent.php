<?php

declare(strict_types=1);

namespace App\Service\Auth\PasswordReset;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserForgotPasswordEvent extends Event
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}