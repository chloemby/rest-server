<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/health')]
class HealthController extends AbstractController
{
    #[Security(name: 'Bearer')]
    #[Response(
        response: \Symfony\Component\HttpFoundation\Response::HTTP_OK,
        description: 'OK',
        content: new JsonContent(
            properties: [
                new Property(property: 'health', type: 'string'),
                new Property(property: 'username', description: 'Имя текущего пользователя', type: 'string'),
                new Property(property: 'email', description: 'Электронный адрес текущего пользователя', type: 'string'),
            ],
            type: 'object'
        )
    )]    #[Route(path: '/check', name: 'api-v1-health-check', methods: [Request::METHOD_GET])]
    public function checkAction(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(
            [
                'health' => 'OK',
                'username' => $user->getUserIdentifier(),
                'email' => $user->getEmail(),
            ]
        );
    }
}