<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\OrganizationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ORM\Table(name: 'organization')]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: TYPES::STRING, length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private ?string $publicId = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 150, unique: true, nullable: false)]
    private ?string $name = null;


    #[ORM\Column(name: 'slug', type: Types::STRING, length: 120, unique: true,nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, nullable: false)]
    private ?bool $enabled = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE,  nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    public function __construct(string $name, string $slug)
    {
        $this->publicId = (string) new Ulid();
        $this->name = $name;
        $this->slug = $slug;
        $this->enabled = true;
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        $this->touch();
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function changeSlug(string $slug): static
    {
        $this->slug = $slug;
        $this->touch();
        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function enable(): static
    {
        $this->enabled = true;
        $this->touch();
        return $this;
    }

    public function disable(): static
    {
        $this->enabled = false;
        $this->touch();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
