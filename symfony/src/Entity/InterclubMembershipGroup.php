<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\InterclubMembershipGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Table(name: 'interclub_membership_group')]
#[ORM\UniqueConstraint(name: 'uniq_img_public_id', columns: ['public_id'])]
#[ORM\UniqueConstraint(name: 'uniq_img_name', columns: ['name'])]
#[ORM\Entity(repositoryClass: InterclubMembershipGroupRepository::class)]
class InterclubMembershipGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\Column(name: 'name',  type: Types::STRING, length: 512)]
    private ?string $name = null;

    #[ORM\Column(name:'description', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, InterclubMembershipGroupMembership>
     */
    #[ORM\OneToMany(targetEntity: InterclubMembershipGroupMembership::class, mappedBy: 'group')]
    private Collection $interclubMembershipGroupMemberships;


    public function __construct()
    {
        $this->publicId = (string) new Ulid();
        $this->interclubMembershipGroupMemberships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
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

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, InterclubMembershipGroupMembership>
     */
    public function getInterclubMembershipGroupMemberships(): Collection
    {
        return $this->interclubMembershipGroupMemberships;
    }


}
