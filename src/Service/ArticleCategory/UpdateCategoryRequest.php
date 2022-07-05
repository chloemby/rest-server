<?php

declare(strict_types=1);

namespace App\Service\ArticleCategory;

use App\Entity\User;

class UpdateCategoryRequest
{
    private int $id;
    private string $title;
    private User $user;

    public function __construct(int $id, string $title, User $user)
    {
        $this->id = $id;
        $this->title = $title;
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}