<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\InterclubMembershipGroupMembershipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Table(name: 'interclub_membership_group_membership')]
#[ORM\UniqueConstraint(name: 'uniq_imgm_public_id', columns: ['public_id'])]
#[ORM\Index(name: 'idx_imgm_membership', columns: ['membership_id'])]
#[ORM\Index(name: 'idx_imgm_group', columns: ['group_id'])]
#[ORM\Index(name: 'idx_imgm_membership_group', columns: ['membership_id', 'group_id'])]
#[ORM\Entity(repositoryClass: InterclubMembershipGroupMembershipRepository::class)]
class InterclubMembershipGroupMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: InterclubMembershipGroup::class, inversedBy: 'interclubMembershipGroupMemberships')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false)]
    private ?InterclubMembershipGroup $group = null;

    #[ORM\ManyToOne(targetEntity: Membership::class, inversedBy: 'interclubMembershipGroupMemberships')]
    #[ORM\JoinColumn(name: 'membership_id', referencedColumnName: 'id', nullable: false)]
    private ?Membership $membership = null;

    #[ORM\Column(name: 'notes', type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->publicId = (string) new Ulid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getGroup(): ?InterclubMembershipGroup
    {
        return $this->group;
    }

    public function setGroup(?InterclubMembershipGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function getMembership(): ?Membership
    {
        return $this->membership;
    }

    public function setMembership(?Membership $membership): static
    {
        $this->membership = $membership;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
}
