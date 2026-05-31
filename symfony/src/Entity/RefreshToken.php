<?php

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use App\Security\RefreshToken\RefreshTokenMode;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
#[ORM\Index(name: 'idx_refresh_token_expires_at', columns: ['expires_at'])]
#[ORM\Index(name: 'idx_refresh_token_revoked_at', columns: ['revoked_at'])]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ConnectionUser::class)]
    #[ORM\JoinColumn(name: 'connection_user_id', referencedColumnName: 'id', nullable: false)]
    private ConnectionUser $connectionUser;

    #[ORM\Column(name: 'token_hash', type: Types::STRING, length: 64, unique: true, nullable: false)]
    private string $tokenHash;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'revoked_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    #[ORM\Column(name: 'revocation_reason', type: Types::STRING, length: 64, nullable: true)]
    private ?string $revocationReason = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'replaced_by_id', referencedColumnName: 'id', nullable: true)]
    private ?self $replacedBy = null;

    #[ORM\Column(name: 'user_agent', type: Types::STRING, length: 512, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'ip_address', type: Types::STRING, length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'mode', length: 16, enumType: RefreshTokenMode::class)]
    private RefreshTokenMode $mode;

    public function __construct(ConnectionUser    $connectionUser,
                                string            $tokenHash,
                                DateTimeImmutable $expiresAt,
                                RefreshTokenMode  $mode,
                                ?string           $userAgent = null,
                                ?string           $ipAddress = null)
    {
        $this->connectionUser = $connectionUser;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
        $this->mode = $mode;
        $this->userAgent = $userAgent !== null ? mb_substr($userAgent, 0, 512) : null;
        $this->ipAddress = $ipAddress;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConnectionUser(): ConnectionUser
    {
        return $this->connectionUser;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function getRevocationReason(): ?string
    {
        return $this->revocationReason;
    }

    public function getReplacedBy(): ?self
    {
        return $this->replacedBy;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function isExpired(DateTimeImmutable $now = new DateTimeImmutable()): bool
    {
        return $this->expiresAt <= $now;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function isUsable(DateTimeImmutable $now = new DateTimeImmutable()): bool
    {
        return !$this->isRevoked() && !$this->isExpired($now);
    }

    public function revoke(string $reason = 'revoked'): void
    {
        if ($this->revokedAt !== null) {
            return;
        }

        $this->revokedAt = new DateTimeImmutable();
        $this->revocationReason = mb_substr($reason, 0, 64);
    }

    public function replaceBy(self $newRefreshToken): void
    {
        $this->revoke('rotated');
        $this->replacedBy = $newRefreshToken;
    }

    public function getMode(): RefreshTokenMode
    {
        return $this->mode;
    }
}
