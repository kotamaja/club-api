<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'auth_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: TYPES::STRING, length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\Column(name:'email', type: TYPES::STRING,  length: 180, unique: true, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(name:'roles', type: Types::JSON, nullable: false)]
    private array $roles = [];

    #[ORM\Column(name:'password_hash', type: TYPES::STRING, length: 255, nullable: true)]
    private ?string $passwordHash = null;

    #[ORM\Column(name: 'status', nullable: false, enumType: UserStatus::class)]
    private ?UserStatus $status = null;

    #[ORM\OneToOne(targetEntity: Person::class, inversedBy: 'user')]
    #[ORM\JoinColumn(name: 'person', unique: true, nullable: true)]
    private ?Person $person = null;

    #[ORM\Column(name:'created_at',type: TYPES::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name:'activated_at',type: TYPES::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $activatedAt = null;

    #[ORM\Column(name:'last_login_at',type: TYPES::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    public function __construct(string $email)
    {
        $this->publicId = (string) new Ulid();
        $this->email = mb_strtolower(trim($email));
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(?string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getActivatedAt(): ?\DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(\DateTimeImmutable $activatedAt): static
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function activate(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->status = UserStatus::Active;
        $this->activatedAt = new \DateTimeImmutable();
    }

    public function disable(): void
    {
        $this->status = UserStatus::Disabled;
    }

}
