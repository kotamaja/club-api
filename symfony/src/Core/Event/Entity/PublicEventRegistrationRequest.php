<?php

namespace App\Core\Event\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Core\Event\Enum\PublicEventRegistrationRequestStatus;
use App\Core\Event\Repository\PublicEventRegistrationRequestRepository;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestCreateDto;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestItemDto;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestListDto;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestRejectDto;
use App\Entity\Contract\OrganizationScopedInterface;
use App\Entity\Organization;
use App\Entity\OrganizationUser;
use App\Entity\Person;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use App\State\PublicEventRegistrationRequest\PublicEventRegistrationRequestAcceptProcessor;
use App\State\PublicEventRegistrationRequest\PublicEventRegistrationRequestCollectionProvider;
use App\State\PublicEventRegistrationRequest\PublicEventRegistrationRequestCreateProcessor;
use App\State\PublicEventRegistrationRequest\PublicEventRegistrationRequestRejectProcessor;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/public/events/{eventId}/registration-requests',
            uriVariables: [
                'eventId' => new Link(
                    fromClass: Event::class,
                    identifiers: ['publicId'],
                ),
            ],
            input: PublicEventRegistrationRequestCreateDto::class,
            output: PublicEventRegistrationRequestItemDto::class,
            read: false,
            processor: PublicEventRegistrationRequestCreateProcessor::class,
        ),
        new GetCollection(
            uriTemplate: '/events/{eventId}/public-registration-requests',
            uriVariables: [
                'eventId' => new Link(
                    toProperty: 'event',
                    fromClass: Event::class,
                    identifiers: ['publicId'],
                ),
            ],
            output: PublicEventRegistrationRequestListDto::class,
            provider: PublicEventRegistrationRequestCollectionProvider::class,
            parameters: [
                'status' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'status',
                ),
                'email' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'email',
                ),
                'lastname' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'lastname',
                ),
                'orderRequestedAt' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'requestedAt',
                ),
                'orderStatus' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'status',
                ),
                'orderLastname' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'lastname',
                ),
                'orderEmail' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'email',
                ),
            ],
        ),
        new Get(
            uriTemplate: '/public-registration-requests/{id}',
            output: PublicEventRegistrationRequestItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/public-registration-requests/{id}/accept',
            status: 200,
            output: PublicEventRegistrationRequestItemDto::class,
            read: true,
            deserialize: false,
            processor: PublicEventRegistrationRequestAcceptProcessor::class,
        ),
        new Post(
            uriTemplate: '/public-registration-requests/{id}/reject',
            status: 200,
            input: PublicEventRegistrationRequestRejectDto::class,
            output: PublicEventRegistrationRequestItemDto::class,
            read: false,
            processor: PublicEventRegistrationRequestRejectProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]
#[ORM\Entity(repositoryClass: PublicEventRegistrationRequestRepository::class)]
#[ORM\Table(name: 'event_public_registration_request')]
class PublicEventRegistrationRequest implements OrganizationScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: TYPES::STRING, length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', nullable: false)]
    private ?Organization $organization = null;

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(name: 'firstname', type: Types::STRING, length: 120)]
    private string $firstname;

    #[ORM\Column(name: 'lastname', type: Types::STRING, length: 120)]
    private string $lastname;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 180)]
    private string $email;

    #[ORM\Column(name: 'note', type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(name: 'status', enumType: PublicEventRegistrationRequestStatus::class)]
    private PublicEventRegistrationRequestStatus $status = PublicEventRegistrationRequestStatus::Pending;

    #[ORM\Column(name: 'requested_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $requestedAt;

    #[ORM\Column(name: 'reviewed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $reviewedAt = null;

    #[ORM\ManyToOne(targetEntity: OrganizationUser::class)]
    #[ORM\JoinColumn(name: 'reviewed_by_id', referencedColumnName: 'id', nullable: true)]
    private ?OrganizationUser $reviewedBy = null;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(name: 'created_person_id', referencedColumnName: 'id', nullable: true)]
    private ?Person $createdPerson = null;

    #[ORM\ManyToOne(targetEntity: EventRegistration::class)]
    #[ORM\JoinColumn(name: 'event_registration_id', referencedColumnName: 'id', nullable: true)]
    private ?EventRegistration $eventRegistration = null;

    #[ORM\Column(name: 'rejection_reason', type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    private function __construct()
    {
        $this->publicId = new Ulid();
    }

    public static function create(Event $event, string $firstname, string $lastname, string $email, ?string $note, DateTimeImmutable $now): self
    {
        if ($event->getClub() === null) {
            throw new \InvalidArgumentException('Public registration requests are only available for club events.');
        }

        $request = new self();

        $request->organization = $event->getOrganization();
        $request->event = $event;
        $request->firstname = trim($firstname);
        $request->lastname = trim($lastname);
        $request->email = mb_strtolower(trim($email));
        $request->changeNote($note);
        $request->requestedAt = $now;

        return $request;
    }

    public function accept(Person $createdPerson, EventRegistration $eventRegistration, OrganizationUser $reviewedBy, DateTimeImmutable $now): void
    {
        if (!$this->status->isPending()) {
            throw new \LogicException('Only pending public registration requests can be accepted.');
        }

        if ($createdPerson->getOrganization() !== $this->getOrganization()) {
            throw new \InvalidArgumentException('Created person must belong to the same organization.');
        }

        if ($eventRegistration->getEvent() !== $this->getEvent()) {
            throw new \InvalidArgumentException('Event registration must belong to the same event.');
        }

        if ($reviewedBy->getOrganization() !== $this->getOrganization()) {
            throw new \InvalidArgumentException('Reviewer must belong to the same organization.');
        }

        $this->status = PublicEventRegistrationRequestStatus::Accepted;
        $this->createdPerson = $createdPerson;
        $this->eventRegistration = $eventRegistration;
        $this->reviewedBy = $reviewedBy;
        $this->reviewedAt = $now;
        $this->rejectionReason = null;
    }

    public function reject(?string $reason, OrganizationUser $reviewedBy, DateTimeImmutable $now): void
    {
        if (!$this->status->isPending()) {
            throw new \LogicException('Only pending public registration requests can be rejected.');
        }

        if ($reviewedBy->getOrganization() !== $this->getOrganization()) {
            throw new \InvalidArgumentException('Reviewer must belong to the same organization.');
        }

        $this->status = PublicEventRegistrationRequestStatus::Rejected;
        $this->reviewedBy = $reviewedBy;
        $this->reviewedAt = $now;
        $this->rejectionReason = $this->normalizeNullableText($reason);
    }

    public function changeNote(?string $note): void
    {
        $this->note = $this->normalizeNullableText($note);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getOrganization(): Organization
    {
        if (!$this->organization instanceof Organization) {
            throw new \LogicException('Public event registration request organization is not initialized.');
        }

        return $this->organization;
    }

    public function getEvent(): Event
    {
        if (!$this->event instanceof Event) {
            throw new \LogicException('Public event registration request event is not initialized.');
        }

        return $this->event;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getStatus(): PublicEventRegistrationRequestStatus
    {
        return $this->status;
    }

    public function getRequestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getReviewedAt(): ?DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function getReviewedBy(): ?OrganizationUser
    {
        return $this->reviewedBy;
    }

    public function getCreatedPerson(): ?Person
    {
        return $this->createdPerson;
    }

    public function getEventRegistration(): ?EventRegistration
    {
        return $this->eventRegistration;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    private function normalizeNullableText(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value !== '' ? $value : null;
    }
}
