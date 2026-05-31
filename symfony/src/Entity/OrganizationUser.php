<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\OrganizationUserRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: OrganizationUserRepository::class)]
#[ORM\Table(name: 'organization_user')]
#[ORM\UniqueConstraint(
    name: 'uniq_organization_user_connection_user_organization',
    columns: ['connection_user_id', 'organization_id']
)]
class OrganizationUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: Types::STRING, length: 26, unique: true)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: ConnectionUser::class)]
    #[ORM\JoinColumn(name: 'connection_user_id', referencedColumnName: 'id', nullable: false)]
    private ?ConnectionUser $connectionUser = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', nullable: false)]
    private ?Organization $organization = null;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(name: 'person_id', referencedColumnName: 'id', nullable: true)]
    private ?Person $person = null;

    #[ORM\Column(name: 'roles', type: Types::JSON, nullable: false)]
    private array $roles = [];

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $enabled = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $createdAt;

    public function __construct(ConnectionUser $connectionUser, Organization $organization, array $roles = [], ?Person $person = null)
    {
        if ($person !== null && $person->getOrganization() !== $organization) {
            throw new \InvalidArgumentException(
                'Person must belong to the same organization as OrganizationUser.'
            );
        }

        $this->publicId = (string)new Ulid();
        $this->connectionUser = $connectionUser;
        $this->organization = $organization;
        $this->person = $person;
        $this->enabled = true;
        $this->roles = array_values(array_unique($roles));
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

    public function getConnectionUser(): ConnectionUser
    {
        if ($this->connectionUser === null) {
            throw new \LogicException('OrganizationUser connectionUser has not been initialized.');
        }

        return $this->connectionUser;
    }

    public function getOrganization(): Organization
    {
        if ($this->organization === null) {
            throw new \LogicException('OrganizationUser organization has not been initialized.');
        }

        return $this->organization;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function linkPerson(Person $person): static
    {
        if ($person->getOrganization() !== $this->getOrganization()) {
            throw new \InvalidArgumentException(
                'Person must belong to the same organization as OrganizationUser.'
            );
        }

        $this->person = $person;

        return $this;
    }

    public function unlinkPerson(): static
    {
        $this->person = null;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): static
    {
        $this->roles = array_values(
            array_filter(
                $this->roles,
                static fn(string $currentRole): bool => $currentRole !== $role
            )
        );

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): static
    {
        $this->enabled = true;
        return $this;
    }

    public function disable(): static
    {
        $this->enabled = false;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
