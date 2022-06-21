<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/health')]
class HealthController extends AbstractController
{
    #[Route(path: '/check', name: 'api-v1-health-check')]
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