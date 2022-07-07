<?php

declare(strict_types=1);

namespace App\Service\Article;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\UserRole;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArticleService
{
    private ArticleRepository $articleRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
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

        if (!$this->isUserArticleOwner($request->getUser(), $article)) {
            throw new AccessDeniedException();
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

        return $this->articleRepository->findBy(
            criteria: ['deletedAt' => null],
            orderBy: ['createdAt' => 'ASC'],
            limit: $perPage,
            offset: $perPage * ($page - 1)
        );
    }

    /**
     * @throws NotFoundException
     */
    public function addCategory(User $user, int $articleId, int $categoryId): Article
    {
        $article = $this->articleRepository->findOneBy(['id' => $articleId, 'deletedAt' => null]);

        if ($article === null) {
            throw new NotFoundException('Такой статьи не существует');
        }

        if (!$user->hasRole(UserRole::VERIFIED_USER) || !$this->isUserArticleOwner($user, $article)) {
            throw new AccessDeniedException();
        }

        $category = $this->categoryRepository->findOneBy(['id' => $categoryId, 'deletedAt' => null]);

        if ($category === null) {
            throw new NotFoundException('Такой категории для статьи не существует');
        }

        $article->addCategory($category);

        $this->articleRepository->save($article, flush: true);

        return $article;
    }

    /**
     * @throws NotFoundException
     */
    public function deleteCategory(User $user, int $articleId, int $categoryId): Article
    {
        $article = $this->articleRepository->findOneBy(['id' => $articleId, 'deletedAt' => null]);

        if ($article === null) {
            throw new NotFoundException('Такой статьи не существует');
        }

        if (!$user->hasRole(UserRole::VERIFIED_USER) || !$this->isUserArticleOwner($user, $article)) {
            throw new AccessDeniedException();
        }

        $category = $this->categoryRepository->findOneBy(['id' => $categoryId, 'deletedAt' => null]);

        if ($category === null) {
            throw new NotFoundException('Такой категории для статьи не существует');
        }

        $article->removeCategory($category);

        $this->articleRepository->save($article, flush: true);

        return $article;
    }
}