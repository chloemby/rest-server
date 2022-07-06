<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * @param User $user
     */
    public function createResetPasswordRequest(
        object $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface {
        return new ResetPasswordRequest(
            requestedAt: new \DateTimeImmutable(),
            expiresAt: $expiresAt,
            hashedToken: $hashedToken,
            user: $user,
            selector: $selector
        );
    }

    /**
     * @param User $user
     */
    public function getUserIdentifier(object $user): string
    {
        return $user->getUserIdentifier();
    }

    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->persist($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    /**
     * @param User $user
     */
    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        /**
         * @var ResetPasswordRequestInterface[] $resetRequests
         */
        $resetRequests = $this->findBy(
            criteria: ['user' => $user],
            orderBy: ['requestedAt' => 'DESC']
        );

        $nonExpiredRequests = \array_filter(
            $resetRequests,
            static fn (ResetPasswordRequestInterface $request) => !$request->isExpired()
        );

        return $nonExpiredRequests === [] ? null : $nonExpiredRequests[0]->getRequestedAt();
    }

    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->delete(ResetPasswordRequest::class, 'r')
            ->where('r.user = :user')
            ->setParameter('user', $resetPasswordRequest->getUser()->getId());

        $queryBuilder->getQuery()->execute();
    }

    public function removeExpiredResetPasswordRequests(): int
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->delete(ResetPasswordRequest::class, 'r')
            ->where('r.expiresAt <= :now')
            ->setParameter('now', new \DateTime());

        return (int)$queryBuilder->getQuery()->execute();
    }
}