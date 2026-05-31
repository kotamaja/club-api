<?php

namespace App\Security\RefreshToken;

use App\Entity\ConnectionUser;
use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RefreshTokenManager
{
    private const TOKEN_BYTES = 64;
    private const TOKEN_TTL = '+30 days';

    public function __construct(private EntityManagerInterface $entityManager, private RefreshTokenRepository $refreshTokenRepository)
    {
    }

    public function createForUser(ConnectionUser $connectionUser, RefreshTokenMode $mode, ?string $userAgent = null, ?string $ipAddress = null): CreatedRefreshToken
    {

        if ($mode === RefreshTokenMode::None) {
            throw new \InvalidArgumentException('Cannot create a refresh token with mode "none".');
        }

        $plainToken = $this->generatePlainToken();
        $tokenHash = $this->hashToken($plainToken);
        $expiresAt = new \DateTimeImmutable(self::TOKEN_TTL);

        $refreshToken = new RefreshToken(
            connectionUser: $connectionUser,
            tokenHash: $tokenHash,
            expiresAt: $expiresAt,
            mode: $mode,
            userAgent: $userAgent,
            ipAddress: $ipAddress,
        );

        $this->entityManager->persist($refreshToken);

        return new CreatedRefreshToken(
            entity: $refreshToken,
            plainToken: $plainToken,
        );
    }

    public function findUsableToken(string $plainToken): ?RefreshToken
    {
        $tokenHash = $this->hashToken($plainToken);

        $refreshToken = $this->refreshTokenRepository->findOneByTokenHash($tokenHash);

        if ($refreshToken === null) {
            return null;
        }

        if (!$refreshToken->isUsable()) {
            return null;
        }

        if (!$refreshToken->getConnectionUser()->isActive()) {
            return null;
        }

        return $refreshToken;
    }

    public function rotatePlainTokenAtomically(string $plainToken, ?string $userAgent = null, ?string $ipAddress = null): CreatedRefreshToken
    {
        $tokenHash = $this->hashToken($plainToken);

        return $this->entityManager->wrapInTransaction(function () use (
            $tokenHash,
            $userAgent,
            $ipAddress,
        ): CreatedRefreshToken {
            $refreshToken = $this->refreshTokenRepository->findOneByTokenHashForUpdate($tokenHash);

            if ($refreshToken === null) {
                throw new InvalidRefreshTokenException('Invalid refresh token.');
            }

            if (!$refreshToken->isUsable()) {
                throw new InvalidRefreshTokenException('Invalid refresh token.');
            }

            if (!$refreshToken->getConnectionUser()->isActive()) {
                throw new InvalidRefreshTokenException('Invalid refresh token.');
            }

            $newCreatedRefreshToken = $this->createForUser(
                connectionUser: $refreshToken->getConnectionUser(),
                mode: $refreshToken->getMode(),
                userAgent: $userAgent,
                ipAddress: $ipAddress,
            );

            $refreshToken->replaceBy($newCreatedRefreshToken->entity);

            $this->entityManager->flush();

            return $newCreatedRefreshToken;
        });
    }

    /**
     * Tu peux garder cette méthode pour les autres usages internes,
     * mais évite de l’utiliser dans /api/auth/refresh.
     */
    public function rotateUsableToken(
        RefreshToken $refreshToken,
        ?string      $userAgent = null,
        ?string      $ipAddress = null,
    ): CreatedRefreshToken
    {
        if (!$refreshToken->isUsable()) {
            throw new \LogicException('Cannot rotate an unusable refresh token.');
        }

        $newCreatedRefreshToken = $this->createForUser(
            connectionUser: $refreshToken->getConnectionUser(),
            mode: $refreshToken->getMode(),
            userAgent: $userAgent,
            ipAddress: $ipAddress,
        );

        $refreshToken->replaceBy($newCreatedRefreshToken->entity);

        return $newCreatedRefreshToken;
    }

    public function revoke(string $plainToken, string $reason = 'logout'): bool
    {
        $tokenHash = $this->hashToken($plainToken);

        $refreshToken = $this->refreshTokenRepository->findOneByTokenHash($tokenHash);

        if ($refreshToken === null) {
            return false;
        }

        $refreshToken->revoke($reason);

        return true;
    }

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function generatePlainToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(self::TOKEN_BYTES)), '+/', '-_'), '=');
    }
}
