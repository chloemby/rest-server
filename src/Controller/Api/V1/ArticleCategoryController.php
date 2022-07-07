<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Service\Article\ArticleService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/articles/{articleId<\d+>}/categories')]
class ArticleCategoryController extends AbstractController
{
    private ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * @throws NotFoundException
     */
    #[Security(name: 'Bearer')]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Добавление категории для статьи',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Article::class))
            ]
        )
    )]
    #[Route(path: '/{categoryId<\d+>}', name: 'api-v1-add-article-category', methods: [Request::METHOD_POST])]
    public function addAction(#[CurrentUser] ?User $user, int $articleId, int $categoryId): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $article = $this->articleService->addCategory($user, $articleId, $categoryId);

        return new JsonResponse(['data' => $article]);
    }

    /**
     * @throws NotFoundException
     */
    #[Security(name: 'Bearer')]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'Удаление категории у статьи',
        content: new JsonContent(
            properties: [
                new Property(property: 'data', ref: new Model(type: Article::class))
            ]
        )
    )]
    #[Route(path: '/{categoryId<\d+>}', name: 'api-v1-delete-article-category', methods: [Request::METHOD_DELETE])]
    public function deleteAction(#[CurrentUser] ?User $user, int $articleId, int $categoryId): JsonResponse
    {
        if ($user === null) {
            throw new AccessDeniedException();
        }

        $article = $this->articleService->deleteCategory($user, $articleId, $categoryId);

        return new JsonResponse(['data' => $article]);
    }
}