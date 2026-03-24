<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\DateFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\Membership\MembershipCreateDto;
use App\Dto\Membership\MembershipItemDto;
use App\Dto\Membership\MembershipListDto;
use App\Dto\Membership\MembershipPatchDto;
use App\Repository\MembershipRepository;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use App\State\Membership\MembershipCreateProcessor;
use App\State\Membership\MembershipDeleteProcessor;
use App\State\Membership\MembershipPatchProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use ApiPlatform\Metadata\Link;
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/memberships',
            output: MembershipListDto::class,
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

                'joinedAt' => new QueryParameter(
                    filter: new DateFilter(),
                    property: 'joinedAt',
                ),
                'endedAt' => new QueryParameter(
                    filter: new DateFilter(),
                    property: 'endedAt',
                    filterContext: DateFilterInterface::EXCLUDE_NULL,
                ),
                'order[:property]' => new QueryParameter(
                    filter: new SortFilter(),
                    properties: [
                        'person.lastname',
                        'person.firstname',
                        'club.name',
                        'joinedAt',
                        'endedAt',
                    ],
                ),
            ],
        ),
        new Get(
            uriTemplate: '/memberships/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Membership::class, identifiers: ['publicId']),
            ],
            output: MembershipItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/memberships',
            input: MembershipCreateDto::class,
            output: MembershipItemDto::class,
            processor: MembershipCreateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/memberships/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Membership::class, identifiers: ['publicId']),
            ],
            input: MembershipPatchDto::class,
            output: MembershipItemDto::class,
            read: true,
            processor: MembershipPatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/memberships/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Membership::class, identifiers: ['publicId']),
            ],
            read: true,
            processor: MembershipDeleteProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]


#[ORM\Table(name: 'membership')]
#[ORM\Entity(repositoryClass: MembershipRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_membership_public_id', columns: ['public_id'])]
#[ORM\Index(name: 'idx_membership_person', columns: ['person_id'])]
#[ORM\Index(name: 'idx_membership_club', columns: ['club_id'])]
#[ORM\Index(name: 'idx_membership_person_club', columns: ['person_id', 'club_id'])]
#[ORM\Index(name: 'idx_membership_person_club_ended_at', columns: ['person_id', 'club_id', 'ended_at'])]
class Membership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: Types::STRING, length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'memberships')]
    #[ORM\JoinColumn(name: 'person_id', referencedColumnName: 'id', nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Club::class, inversedBy: 'memberships')]
    #[ORM\JoinColumn(name: 'club_id', referencedColumnName: 'id', nullable: false)]
    private ?Club $club = null;

    #[ORM\Column(name: 'joined_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\Column(name: 'ended_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    /**
     * @var Collection<int, InterclubMembershipGroupMembership>
     */
    #[ORM\OneToMany(targetEntity: InterclubMembershipGroupMembership::class, mappedBy: 'membership')]
    private Collection $interclubMembershipGroupMemberships;

    /**
     * @var Collection<int, ClubMembershipGroupMembership>
     */
    #[ORM\OneToMany(targetEntity: ClubMembershipGroupMembership::class, mappedBy: 'membership')]
    private Collection $clubMembershipGroupMemberships;

    public function __construct()
    {
        $this->publicId = (string) new Ulid();
        $this->interclubMembershipGroupMemberships = new ArrayCollection();
        $this->clubMembershipGroupMemberships = new ArrayCollection();
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if (null === $this->joinedAt || null === $this->endedAt) {
            return;
        }

        if ($this->endedAt < $this->joinedAt) {
            $context
                ->buildViolation('The end date must be greater than or equal to the join date.')
                ->atPath('endedAt')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
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

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(?\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function isActive(): bool
    {
        return null === $this->endedAt;
    }

    /**
     * @return Collection<int, InterclubMembershipGroupMembership>
     */
    public function getInterclubMembershipGroupMemberships(): Collection
    {
        return $this->interclubMembershipGroupMemberships;
    }

    /**
     * @return Collection<int, ClubMembershipGroupMembership>
     */
    public function getClubMembershipGroupMemberships(): Collection
    {
        return $this->clubMembershipGroupMemberships;
    }


}
