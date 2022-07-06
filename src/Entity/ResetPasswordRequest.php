<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

#[Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    #[Id]
    #[Column(type: 'string', length: 255)]
    private string $selector;

    #[Column(name: 'requested_at', type: 'datetime')]
    private \DateTimeInterface $requestedAt;

    #[Column(name: 'expires_at', type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    #[Column(name: 'hashed_token', type: 'string', length: 255)]
    private string $hashedToken;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private UserInterface $user;

    public function __construct(
        \DateTimeInterface $requestedAt,
        \DateTimeInterface $expiresAt,
        string $hashedToken,
        UserInterface $user,
        string $selector
    ) {
        $this->requestedAt = $requestedAt;
        $this->expiresAt = $expiresAt;
        $this->hashedToken = $hashedToken;
        $this->user = $user;
        $this->selector = $selector;
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function isExpired(): bool
    {
        return (new \DateTime())->getTimestamp() > $this->expiresAt->getTimestamp();
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }

    /**
     * @return User
     */
    public function getUser(): object
    {
        return $this->user;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }
}