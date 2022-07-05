<?php

declare(strict_types=1);

namespace App\Service\Article;

use App\Entity\User;

class UpdateArticleRequest
{
    private int $id;
    private User $user;
    private string $title;
    private string $text;

    public function __construct(
        int $id,
        User $user,
        string $title,
        string $text
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->title = $title;
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }
}