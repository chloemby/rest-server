<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\AppException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\Article\ArticleService;
use App\Service\Article\CreateArticleRequest;
use App\Service\Article\UpdateArticleRequest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/articles')]
class ArticleController extends AbstractController
{
    private ArticleService $service;

    public function __construct(ArticleService $service)
    {
        $this->service = $service;
    }

    /**
     * @throws AppException
     */
    #[Security(name: 'Bearer')]
    #[Parameter(name: 'title', description: 'Название статьи', required: true)]
    #[Parameter(name: 'text', description: 'Текст статьи', required: true)]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Создание статьи',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Article::class))
            ]
        )
    )]
    #[Route(name: 'api-v1-create-article', methods: [Request::METHOD_POST])]
    public function createAction(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $createArticleRequest = new CreateArticleRequest(
            title: $request->get('title', ''),
            text: $request->get('text', ''),
            user: $user
        );

        $article = $this->service->create($createArticleRequest);

        return new JsonResponse(['data' => $article]);
    }

    /**
     * @throws AppException
     */
    #[Security(name: 'Bearer')]
    #[RequestBody(
        content: new JsonContent(
            properties: [
                new Property(property: 'title', description: 'Название статьи', type: 'string'),
                new Property(property: 'text', description: 'Текст статьи', type: 'string'),
            ]
        )
    )]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Обновление статьи',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Article::class))
            ]
        )
    )]
    #[Route(path: '/{id<\d+>}', name: 'api-v1-update-article', methods: [Request::METHOD_PUT])]
    public function updateAction(Request $request, int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        try {
            $content = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ValidationException('Не передано никаких параметров');
        }

        $updateArticleRequest = new UpdateArticleRequest(
            id: $id,
            user: $user,
            title: (string)($content['title'] ?? ''),
            text: (string)($content['text'] ?? '')
        );

        $article = $this->service->update($updateArticleRequest);

        return new JsonResponse(['data' => $article]);
    }

    /**
     * @throws NotFoundException
     */
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Получение статьи',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type:  Article::class))
            ]
        )
    )]
    #[Route(path: '/{id<\d+>}', name: 'api-v1-get-article-by-id', methods: [Request::METHOD_GET])]
    public function getByIdAction(int $id): JsonResponse
    {
        $article = $this->service->get($id);

        return new JsonResponse(['data' => $article]);
    }

    /**
     * @throws NotFoundException
     */
    #[Security(name: 'Bearer')]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Удаление статьи',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type:  Article::class))
            ]
        )
    )]
    #[Route(path: '/{id<\d+>}', name: 'api-v1-delete-article-by-id', methods: [Request::METHOD_DELETE])]
    public function deleteAction(int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $article = $this->service->delete($id, $user);

        return new JsonResponse(['data' => $article]);
    }
}