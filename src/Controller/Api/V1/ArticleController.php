<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Exception\AppException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\Article\ArticleService;
use App\Service\Article\CreateArticleRequest;
use App\Service\Article\UpdateArticleRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1')]
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
    #[Route(path: '/articles', name: 'api-v1-create-article', methods: [Request::METHOD_POST])]
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
    #[Route(path: '/articles/{id<\d+>}', name: 'api-v1-update-article', methods: [Request::METHOD_PUT])]
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
    #[Route(path: '/articles/{id<\d+>}', name: 'api-v1-get-article-by-id', methods: [Request::METHOD_GET])]
    public function getByIdAction(int $id): JsonResponse
    {
        $article = $this->service->get($id);

        return new JsonResponse(['data' => $article]);
    }

    /**
     * @throws NotFoundException
     */
    #[Route(path: '/articles/{id<\d+>}', name: 'api-v1-delete-article-by-id', methods: [Request::METHOD_DELETE])]
    public function deleteAction(int $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $article = $this->service->delete($id, $user);

        return new JsonResponse(['data' => $article]);
    }
}