<?php

declare(strict_types=1);

namespace App\Service\Article;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\ArticleRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArticleService
{
    private ArticleRepository $repository;

    public function __construct(
        ArticleRepository $repository,
    ) {
        $this->repository = $repository;
    }

    public function create(CreateArticleRequest $request): Article
    {
        $article = new Article($request->getUser());

        $article->setTitle($request->getTitle())->setText($request->getText());

        $this->repository->save($article, flush: true);

        return $article;
    }

    /**
     * @throws NotFoundException
     */
    public function update(UpdateArticleRequest $request): Article
    {
        $article = $this->repository->find($request->getId());

        if ($article === null) {
            throw new NotFoundException('Статья не найдена');
        }

        $article->setTitle($request->getTitle())
            ->setText($request->getText())
            ->setUpdatedBy($request->getUser()->getId())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->repository->save($article, flush: true);

        return $article;
    }

    /**
     * @throws NotFoundException
     */
    public function get(int $id): ?Article
    {
        $article = $this->repository->find($id);

        if ($article === null) {
            throw new NotFoundException('Статья не найдена');
        }

        return $article;
    }

    /**
     * @throws NotFoundException
     */
    public function delete(int $id, User $user): Article
    {
        $article = $this->repository->find($id);

        if ($article === null) {
            throw new NotFoundException('Статья не найдена');
        }

        if (!$this->isUserArticleOwner($user, $article)) {
            throw new AccessDeniedException();
        }

        $this->repository->delete($article, $user);

        return $article;
    }

    public function isUserArticleOwner(User $user, Article $article): bool
    {
        return $article->getCreatedBy() === $user->getId();
    }

    /**
     * @return Article[]
     * @throws ValidationException
     */
    public function getList(int $page, int $perPage): array
    {
        if ($page < 1) {
            throw new ValidationException('Такой страницы не существует');
        }

        if ($perPage < 1) {
            throw new ValidationException('На странице не может быть меньше 1 статьи');
        }

        return $this->repository->findBy(
            criteria: ['deletedAt' => null],
            orderBy: ['createdAt' => 'ASC'],
            limit: $perPage,
            offset: $perPage * ($page - 1)
        );
    }
}