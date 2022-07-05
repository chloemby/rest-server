<?php

declare(strict_types=1);

namespace App\Service\Article;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Repository\ArticleRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct(
        ArticleRepository $articleRepository,
    ) {
        $this->articleRepository = $articleRepository;
    }

    public function create(CreateArticleRequest $request): Article
    {
        $article = new Article($request->getUser());

        $article->setTitle($request->getTitle())->setText($request->getText());

        $this->articleRepository->save($article, flush: true);

        return $article;
    }

    /**
     * @throws NotFoundException
     */
    public function update(UpdateArticleRequest $request): Article
    {
        $article = $this->articleRepository->find($request->getId());

        if ($article === null) {
            throw new NotFoundException('Статья не найдена');
        }

        $article->setTitle($request->getTitle())
            ->setText($request->getText())
            ->setUpdatedBy($request->getUser()->getId())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->articleRepository->save($article, flush: true);

        return $article;
    }

    /**
     * @throws NotFoundException
     */
    public function get(int $id): ?Article
    {
        $article = $this->articleRepository->find($id);

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
        $article = $this->articleRepository->find($id);

        if ($article === null) {
            throw new NotFoundException('Статья не найдена');
        }

        if (!$this->isUserArticleOwner($user, $article)) {
            throw new AccessDeniedException();
        }

        $this->articleRepository->delete($article, $user);

        return $article;
    }

    public function isUserArticleOwner(User $user, Article $article): bool
    {
        return $article->getCreatedBy() === $user->getId();
    }
}