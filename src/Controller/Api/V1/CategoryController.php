<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\Category;
use JsonException;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\ArticleCategory\CategoryService;
use App\Service\ArticleCategory\CreateCategoryRequest;
use App\Service\ArticleCategory\UpdateCategoryRequest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/categories')]
class CategoryController extends AbstractController
{
    private CategoryService $service;

    public function __construct(
        CategoryService $service,
    ) {
        $this->service = $service;
    }

    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Получение категории по ID',
        content: new JsonContent(properties: [new Property(property: 'data', ref: new Model(type: Category::class))])
    )]
    #[Route(path: '/{id<\d+>}', name: 'api-v1-get-category-by-id', methods: [Request::METHOD_GET])]
    public function getByIdAction(int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $articleCategory = $this->service->getById($id);

        return new JsonResponse(['data' => $articleCategory]);
    }

    /**
     * @throws ValidationException
     */
    #[Security(name: 'Bearer')]
    #[Parameter(name: 'title', description: 'Название категории', required: true)]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Создание категории',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Category::class))
            ]
        )
    )]
    #[Route(name: 'api-v1-create-category', methods: [Request::METHOD_POST])]
    public function createAction(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $createCategoryRequest = new CreateCategoryRequest(
            (string)$request->get('title', ''),
            $user
        );

        $category = $this->service->create($createCategoryRequest);

        return new JsonResponse(['data' => $category]);
    }

    /**
     * @throws ValidationException|NotFoundException|JsonException
     */
    #[Security(name: 'Bearer')]
    #[RequestBody(
        content: new JsonContent(
            properties: [new Property(property: 'title', description: 'Название категории', type: 'string')]
        )
    )]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Обновление категории',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Category::class))
            ]
        )
    )]
    #[Route(path: '/{id<\d+>}', name: 'api-v1-update-category', methods: [Request::METHOD_PUT])]
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

        $updateCategoryRequest = new UpdateCategoryRequest(
            $id,
            (string)$content['title'],
            $user
        );

        $category = $this->service->update($updateCategoryRequest);

        return new JsonResponse(['data' => $category]);
    }

    /**
     * @throws NotFoundException
     */
    #[Security(name: 'Bearer')]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Удаление категории',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Category::class))
            ]
        )
    )]
    #[Route(path: '/{id<\d+>}', name: 'api-v1-delete-category', methods: [Request::METHOD_DELETE])]
    public function deleteAction(int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(['data' => $this->service->delete($id, $user)]);
    }

    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Получение списка категорий',
        content: new JsonContent(
            properties: [
                new Property(
                    property: 'data',
                    type: 'array',
                    items: new Items(ref: new Model(type: Category::class))

                )
            ]
        )
    )]
    #[Route(name: 'api-v1-get-categories', methods: [Request::METHOD_GET])]
    public function getAction(): JsonResponse
    {
        return new JsonResponse(['data' => $this->service->getAll()]);
    }
}
