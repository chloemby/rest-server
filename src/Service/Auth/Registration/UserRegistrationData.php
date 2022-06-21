<?php

declare(strict_types=1);

namespace App\Service\Auth\Registration;

class UserRegistrationData
{
    private string $username;
    private string $password;
    private string $email;

    public function __construct(string $username, string $password, string $email)
    {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}