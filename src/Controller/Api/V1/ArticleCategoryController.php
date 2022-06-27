<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use JsonException;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\ArticleCategory\ArticleCategoryService;
use App\Service\ArticleCategory\CreateArticleCategoryRequest;
use App\Service\ArticleCategory\UpdateArticleCategoryRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/article_category')]
class ArticleCategoryController extends AbstractController
{
    private ArticleCategoryService $service;

    public function __construct(ArticleCategoryService $service)
    {
        $this->service = $service;
    }

    #[Route(path: '/{id<\d+>}', name: 'api-v1-get_article-category-by-id', methods: [Request::METHOD_GET])]
    public function getByIdAction(int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $articleCategory = $this->service->getById($id);

        return new JsonResponse($articleCategory);
    }

    /**
     * @throws ValidationException
     */
    #[Route(name: 'api-v1-create-article-category', methods: [Request::METHOD_POST])]
    public function createAction(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $createArticleCategoryRequest = new CreateArticleCategoryRequest(
            (string)$request->get('title', ''),
            $user
        );

        $articleCategory = $this->service->create($createArticleCategoryRequest);

        return new JsonResponse(['data' => $articleCategory]);
    }

    /**
     * @throws ValidationException|NotFoundException
     * @throws JsonException
     */
    #[Route(path: '/{id<\d+>}', name: 'api-v1-update-article-category', methods: [Request::METHOD_PUT])]
    public function updateAction(int $id, Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        try {
            $content = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ValidationException('Не передано никаких параметров');
        }

        $updateArticleCategoryRequest = new UpdateArticleCategoryRequest(
            $id,
            (string)$content['title'],
            $user
        );

        $articleCategory = $this->service->update($updateArticleCategoryRequest);

        return new JsonResponse(['data' => $articleCategory]);
    }

    /**
     * @throws NotFoundException
     */
    #[Route(path: '/{id<\d+>}', name: 'api-v1-delete-article-category', methods: [Request::METHOD_DELETE])]
    public function deleteAction(int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(['data' => $this->service->delete($id, $user)]);
    }
}
