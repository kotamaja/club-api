<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupCreateDto;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupItemDto;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupListDto;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Repository\ClubMembershipGroupRepository;
use App\State\Club\ClubDeleteProcessor;
use App\State\ClubMembershipGroup\ClubMembershipGroupCreateProcessor;
use App\State\ClubMembershipGroup\ClubMembershipGroupDeleteProcessor;
use App\State\ClubMembershipGroup\ClubMembershipGroupPatchProcessor;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/club_membership_groups',
            output: ClubMembershipGroupListDto::class,
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
                'clubId' => new QueryParameter(
                    schema: [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    filter: new ExactFilter(),
                    property: 'club.publicId',
                    constraints: [
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Ulid(),
                        ]),
                    ],
                    castToArray: true,
                ),
                'name' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'name',
                ),
                'description' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'description',
                ),
                'order[:property]' => new QueryParameter(
                    filter: new SortFilter(),
                    properties: [
                        'club.name',
                        'name',
                    ],
                ),
            ],
        ),
        new Get(
            uriTemplate: '/club_membership_groups/{id}',
            uriVariables: [
                'id' => new Link(fromClass: ClubMembershipGroup::class, identifiers: ['publicId']),
            ],
            output: ClubMembershipGroupItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/club_membership_groups',
            input: ClubMembershipGroupCreateDto::class,
            output: ClubMembershipGroupItemDto::class,
            processor: ClubMembershipGroupCreateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/club_membership_groups/{id}',
            uriVariables: [
                'id' => new Link(fromClass: ClubMembershipGroup::class, identifiers: ['publicId']),
            ],
            input: ClubMembershipGroupPatchDto::class,
            output: ClubMembershipGroupItemDto::class,
            read: true,
            processor: ClubMembershipGroupPatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/club_membership_groups/{id}',
            uriVariables: [
                'id' => new Link(fromClass: ClubMembershipGroup::class, identifiers: ['publicId']),
            ],
            read: true,
            processor: ClubMembershipGroupDeleteProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]

#[ORM\Table(name: 'club_membership_group')]
#[ORM\UniqueConstraint(name: 'uniq_cmg_public_id', columns: ['public_id'])]
#[ORM\UniqueConstraint(name: 'uniq_cmg_name_club_id', columns: ['name', 'club_id'])]
#[ORM\Index(name: 'idx_cmg_club', columns: ['club_id'])]
#[ORM\Entity(repositoryClass: ClubMembershipGroupRepository::class)]
class ClubMembershipGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;


    #[ORM\ManyToOne(targetEntity: Club::class, inversedBy: 'clubMembershipGroups')]
    #[ORM\JoinColumn(name: 'club_id', referencedColumnName: 'id', nullable: false,)]
    private ?Club $club = null;

    #[ORM\Column(name: 'name',  type: Types::STRING, length: 512)]
    private ?string $name = null;

    #[ORM\Column(name:'description', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, ClubMembershipGroupMembership>
     */
    #[ORM\OneToMany(targetEntity: ClubMembershipGroupMembership::class, mappedBy: 'group')]
    private Collection $clubMembershipGroupMemberships;


    public function __construct()
    {
        $this->publicId = (string) new Ulid();
        $this->clubMembershipGroupMemberships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
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
     * @return Collection<int, ClubMembershipGroupMembership>
     */
    public function getClubMembershipGroupMemberships(): Collection
    {
        return $this->clubMembershipGroupMemberships;
    }


}
