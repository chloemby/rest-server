<?php

declare(strict_types=1);

namespace App\Service\ArticleCategory;

use App\Entity\ArticleCategory;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\ArticleCategoryRepository;

class ArticleCategoryService
{
    private ArticleCategoryRepository $repository;
    private ArticleCategoryValidator $validator;

    public function __construct(ArticleCategoryRepository $repository, ArticleCategoryValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function create(CreateArticleCategoryRequest $request): ArticleCategory
    {
        $articleCategory = (new ArticleCategory($request->getUser()))
            ->setTitle($request->getTitle());

        $this->validator->validate($articleCategory);

        $this->repository->save($articleCategory, flush: true);

        return $articleCategory;
    }

    public function getById(int $id): ?ArticleCategory
    {
        return $this->repository->find($id);
    }

    public function getAll(): array
    {
        return $this->repository->findBy(['deleted_at' => null]);
    }

    /**
     * @throws NotFoundException
     */
    public function delete(int $id, User $user): ArticleCategory
    {
        $articleCategory = $this->repository->find($id);

        if ($articleCategory === null) {
            throw new NotFoundException();
        }

        $articleCategory->setDeletedAt(new \DateTimeImmutable())->setDeletedBy($user->getId());

        $this->repository->save($articleCategory, flush: true);

        return $articleCategory;
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function update(UpdateArticleCategoryRequest $request): ArticleCategory
    {
        $articleCategory = $this->repository->find($request->getId());

        if ($articleCategory === null) {
            throw new NotFoundException();
        }

        $articleCategory->setTitle($request->getTitle())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setUpdatedBy($request->getUser()->getId());

        $this->validator->validate($articleCategory);

        $this->repository->save($articleCategory, flush: true);

        return $articleCategory;
    }
}