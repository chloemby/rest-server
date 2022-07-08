<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Exception\NotFoundException;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/users')]
class UserController extends AbstractController
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * @throws NotFoundException
     */
    #[Route(path: '/{id<\d+>}', name: 'api-v1-get-user-by-id', methods: [Request::METHOD_GET])]
    public function getById(int $id): JsonResponse
    {
        $user = $this->service->getById($id);

        return new JsonResponse(['data' => $user]);
    }
}