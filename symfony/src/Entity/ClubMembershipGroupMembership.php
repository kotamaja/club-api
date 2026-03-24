<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipCreateDto;
use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipItemDto;
use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipListDto;
use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipPatchDto;
use App\Repository\ClubMembershipGroupMembershipRepository;
use App\State\ClubMembershipGroupMembership\ClubMembershipGroupMembershipCreateProcessor;
use App\State\ClubMembershipGroupMembership\ClubMembershipGroupMembershipDeleteProcessor;
use App\State\ClubMembershipGroupMembership\ClubMembershipGroupMembershipPatchProcessor;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/club_membership_group_memberships',
            output: ClubMembershipGroupMembershipListDto::class,
            provider: CollectionProvider::class,
            parameters: [
                'id' => new QueryParameter(
                    schema: [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    filter: new ExactFilter(),
                    property: 'publicId',
                    constraints: [
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Ulid(),
                        ]),
                    ],
                    castToArray: true,
                ),
                'personId' => new QueryParameter(
                    schema: [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    filter: new ExactFilter(),
                    property: 'person.publicId',
                    constraints: [
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Ulid(),
                        ]),
                    ],
                    castToArray: true,
                ),

            ],
        ),
        new Get(
            uriTemplate: '/club_membership_group_memberships/{id}',
            uriVariables: [
                'id' => new Link(fromClass: ClubMembershipGroupMembership::class, identifiers: ['publicId']),
            ],
            output: ClubMembershipGroupMembershipItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/club_membership_group_memberships',
            input: ClubMembershipGroupMembershipCreateDto::class,
            output: ClubMembershipGroupMembershipItemDto::class,
            processor: ClubMembershipGroupMembershipCreateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/club_membership_group_memberships/{id}',
            uriVariables: [
                'id' => new Link(fromClass: ClubMembershipGroupMembership::class, identifiers: ['publicId']),
            ],
            input: ClubMembershipGroupMembershipPatchDto::class,
            output: ClubMembershipGroupMembershipItemDto::class,
            read: true,
            processor: ClubMembershipGroupMembershipPatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/club_membership_group_memberships/{id}',
            uriVariables: [
                'id' => new Link(fromClass: ClubMembershipGroupMembership::class, identifiers: ['publicId']),
            ],
            read: true,
            processor: ClubMembershipGroupMembershipDeleteProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]
#[ORM\Table(name: 'club_membership_group_membership')]
#[ORM\UniqueConstraint(name: 'uniq_cmgm_public_id', columns: ['public_id'])]
#[ORM\UniqueConstraint(name: 'uniq_cmgm_membership_group', columns: ['membership_id', 'group_id'])]
#[ORM\Index(name: 'idx_cmgm_membership', columns: ['membership_id'])]
#[ORM\Index(name: 'idx_cmgm_group', columns: ['group_id'])]
#[ORM\Entity(repositoryClass: ClubMembershipGroupMembershipRepository::class)]
class ClubMembershipGroupMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: ClubMembershipGroup::class, inversedBy: 'clubMembershipGroupMemberships')]
    #[ORM\JoinColumn(name: 'group_id', nullable: false)]
    private ?ClubMembershipGroup $group = null;

    #[ORM\ManyToOne(targetEntity: Membership::class, inversedBy: 'clubMembershipGroupMemberships')]
    #[ORM\JoinColumn(name: 'membership_id', nullable: false)]
    private ?Membership $membership = null;

    #[ORM\Column(name: 'notes', type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->publicId = (string)new Ulid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }


    public function getGroup(): ?ClubMembershipGroup
    {
        return $this->group;
    }

    public function setGroup(?ClubMembershipGroup $group): static
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
