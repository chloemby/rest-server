<?php

declare(strict_types=1);

namespace App\Service\ArticleCategory;

use App\Entity\Category;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\CategoryRepository;

class CategoryService
{
    private CategoryRepository $repository;
    private CategoryValidator $validator;

    public function __construct(
        CategoryRepository $repository,
        CategoryValidator $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function create(CreateCategoryRequest $request): Category
    {
        $category = (new Category($request->getUser()))
            ->setTitle($request->getTitle());

        $this->validator->validate($category);

        $this->repository->save($category, flush: true);

        return $category;
    }

    public function getById(int $id): ?Category
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
    public function delete(int $id, User $user): Category
    {
        $category = $this->repository->find($id);

        if ($category === null) {
            throw new NotFoundException();
        }

        $category->setDeletedAt(new \DateTimeImmutable())->setDeletedBy($user->getId());

        $this->repository->save($category, flush: true);

        return $category;
    }

    /**
     * @throws NotFoundException | ValidationException
     */
    public function update(UpdateCategoryRequest $request): Category
    {
        $category = $this->repository->find($request->getId());

        if ($category === null) {
            throw new NotFoundException();
        }

        $category->setTitle($request->getTitle())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setUpdatedBy($request->getUser()->getId());

        $this->validator->validate($category);

        $this->repository->save($category, flush: true);

        return $category;
    }
}