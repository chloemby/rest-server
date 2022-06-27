<?php

declare(strict_types=1);

namespace App\Service\ArticleCategory;

use App\Entity\User;

class CreateArticleCategoryRequest
{
    private string $title;
    private User $user;

    public function __construct(string $title, User $user)
    {
        $this->title = $title;
        $this->user = $user;
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