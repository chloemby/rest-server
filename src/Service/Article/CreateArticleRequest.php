<?php

declare(strict_types=1);

namespace App\Service\Article;

use App\Entity\User;

class CreateArticleRequest
{
    private string $title;
    private string $text;
    private User $user;

    public function __construct(
        string $title,
        string $text,
        User $user
    ) {
        $this->title = $title;
        $this->text = $text;
        $this->user = $user;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}