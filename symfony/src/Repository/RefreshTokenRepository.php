<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
final class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findOneByTokenHash(string $tokenHash): ?RefreshToken
    {
        return $this->findOneBy([
            'tokenHash' => $tokenHash,
        ]);
    }

    public function findOneByTokenHashForUpdate(string $tokenHash): ?RefreshToken
    {
        return $this->createQueryBuilder('rt')
            ->andWhere('rt.tokenHash = :tokenHash')
            ->setParameter('tokenHash', $tokenHash)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }

    public function deleteExpiredOrOldRevoked(DateTimeImmutable $expiredBefore, DateTimeImmutable $revokedBefore): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.expiresAt < :expiredBefore')
            ->orWhere('rt.revokedAt IS NOT NULL AND rt.revokedAt < :revokedBefore')
            ->setParameter('expiredBefore', $expiredBefore)
            ->setParameter('revokedBefore', $revokedBefore)
            ->getQuery()
            ->execute();
    }
}
