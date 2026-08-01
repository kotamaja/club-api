<?php

namespace App\Core\Event\Entity;

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
use App\Core\Event\Enum\EventStatus;
use App\Core\Event\Enum\EventType;
use App\Core\Event\Repository\EventRepository;
use App\Dto\Event\EventCreateDto;
use App\Dto\Event\EventItemDto;
use App\Dto\Event\EventListDto;
use App\Dto\Event\EventPatchDto;
use App\Entity\Club;
use App\Entity\Contract\OrganizationScopedInterface;
use App\Entity\Organization;
use App\Entity\Person;
use App\State\CollectionProvider;
use App\State\Event\EventArchiveProcessor;
use App\State\Event\EventCancelProcessor;
use App\State\Event\EventCreateProcessor;
use App\State\Event\EventDeleteProcessor;
use App\State\Event\EventPatchProcessor;
use App\State\Event\EventPublishProcessor;
use App\State\ItemProvider;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/events',
            output: EventListDto::class,
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
                'title' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'title',
                ),
                'location' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'location',
                ),
                'type' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'type',
                ),
                'status' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'status',
                ),
                'orderId' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'publicId',
                ),
                'orderTitle' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'title',
                ),
                'orderStartsAt' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'startsAt',
                ),
                'orderEndsAt' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'endsAt',
                ),
                'orderStatus' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'status',
                ),
            ],
        ),
        new Get(
            uriTemplate: '/events/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Event::class, identifiers: ['publicId']),
            ],
            output: EventItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/events',
            input: EventCreateDto::class,
            output: EventItemDto::class,
            processor: EventCreateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/events/{id}',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            input: EventPatchDto::class,
            output: EventItemDto::class,
            read: true,
            processor: EventPatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/events/{id}',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            read: true,
            processor: EventDeleteProcessor::class,
        ),
        new Post(
            uriTemplate: '/events/{id}/publish',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            status: 200,
            output: EventItemDto::class,
            read: true,
            deserialize: false,
            processor: EventPublishProcessor::class,
        ),
        new Post(
            uriTemplate: '/events/{id}/cancel',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            status: 200,
            output: EventItemDto::class,
            read: true,
            deserialize: false,
            processor: EventCancelProcessor::class,
        ),
        new Post(
            uriTemplate: '/events/{id}/archive',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            status: 200,
            output: EventItemDto::class,
            read: true,
            deserialize: false,
            processor: EventArchiveProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event  implements OrganizationScopedInterface
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
    #[ORM\JoinColumn(name: 'organization_id', nullable: false, onDelete: 'RESTRICT')]
    private Organization $organization;

    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(name: 'club_id', nullable: true, onDelete: 'SET NULL')]
    private ?Club $club = null;

    #[ORM\Column(name: 'event_type', nullable: false, enumType: EventType::class)]
    private EventType $type = EventType::General;

    #[ORM\Column(name: 'event_status', nullable: false, enumType: EventStatus::class)]
    private EventStatus $status = EventStatus::Draft;

    #[ORM\Column(name: 'title', type: TYPES::STRING, length: 180, nullable: false)]
    private string $title;

    #[ORM\Column(name: 'description', type: TYPES::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'location', type: TYPES::STRING, length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(name: 'start_at', type: TYPES::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $startsAt;

    #[ORM\Column(name: 'end_at', type: TYPES::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $endsAt;

    #[ORM\Column(name: 'timezone', type: TYPES::STRING, length: 64, nullable: false)]
    private string $timezone = 'Europe/Zurich';

    #[ORM\Column(name: 'all_day', type: Types::BOOLEAN, nullable: false)]
    private bool $allDay = false;

    #[ORM\Column(name: 'capacity', type: Types::INTEGER, nullable: true)]
    private ?int $capacity = null;

    #[ORM\Column(name: 'waitlist_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $waitlistEnabled = false;

    #[ORM\Column(name: 'public_registration_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $publicRegistrationEnabled = false;

    #[ORM\Column(name: 'registration_starts_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $registrationStartsAt = null;

    #[ORM\Column(name: 'registration_ends_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $registrationEndsAt = null;

    /**
     * @var Collection<int, EventRegistration>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventRegistration::class, cascade: ['persist'], orphanRemoval: false,)]
    private Collection $registrations;


    private function __construct(Organization $organization, string $title, DateTimeImmutable $startsAt, DateTimeImmutable $endsAt)
    {
        $this->publicId = new Ulid();
        $this->organization = $organization;
        $this->title = trim($title);
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->registrations = new ArrayCollection();

        $this->validateTitle();
        $this->validateDates();
    }

    public static function create(Organization $organization, string $title, DateTimeImmutable $startsAt, DateTimeImmutable $endsAt): self
    {
        return new self($organization, $title, $startsAt, $endsAt);
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
            throw new \LogicException('Event organization is not initialized.');
        }

        return $this->organization;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function attachToClub(?Club $club): void
    {
        if ($club !== null && $club->getOrganization() !== $this->getOrganization()) {
            throw new \InvalidArgumentException('The club must belong to the same organization as the event.');
        }

        $this->club = $club;
    }

    public function getType(): EventType
    {
        return $this->type;
    }

    public function changeType(EventType $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): EventStatus
    {
        return $this->status;
    }

    public function publish(): void
    {
        $this->status = EventStatus::Published;
    }

    public function cancel(): void
    {
        $this->status = EventStatus::Cancelled;
    }

    public function archive(): void
    {
        $this->status = EventStatus::Archived;
    }

    public function revertToDraft(): void
    {
        $this->status = EventStatus::Draft;
    }

    public function acceptsRegistrations(): bool
    {
        return $this->status->acceptsRegistrations();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function rename(string $title): void
    {
        $this->title = trim($title);
        $this->validateTitle();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function changeDescription(?string $description): void
    {
        $description = $description !== null ? trim($description) : null;

        $this->description = $description !== '' ? $description : null;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function changeLocation(?string $location): void
    {
        $location = $location !== null ? trim($location) : null;

        $this->location = $location !== '' ? $location : null;
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function reschedule(\DateTimeImmutable $startsAt, \DateTimeImmutable $endsAt): void
    {
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;

        $this->validateDates();
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function changeTimezone(string $timezone): void
    {
        if (!in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw new \InvalidArgumentException(sprintf('Invalid timezone "%s".', $timezone));
        }

        $this->timezone = $timezone;
    }

    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    public function markAsAllDay(): void
    {
        $this->allDay = true;
    }

    public function markAsTimed(): void
    {
        $this->allDay = false;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function changeCapacity(?int $capacity): void
    {
        if ($capacity !== null && $capacity < 1) {
            throw new \InvalidArgumentException('Capacity must be null or greater than or equal to 1.');
        }

        $this->capacity = $capacity;
    }

    public function hasLimitedCapacity(): bool
    {
        return $this->capacity !== null;
    }

    public function isWaitlistEnabled(): bool
    {
        return $this->waitlistEnabled;
    }

    public function enableWaitlist(): void
    {
        $this->waitlistEnabled = true;
    }

    public function disableWaitlist(): void
    {
        $this->waitlistEnabled = false;
    }

    public function getRegistrationStartsAt(): ?DateTimeImmutable
    {
        return $this->registrationStartsAt;
    }

    public function getRegistrationEndsAt(): ?DateTimeImmutable
    {
        return $this->registrationEndsAt;
    }

    public function changeRegistrationWindow(?\DateTimeImmutable $startsAt, ?\DateTimeImmutable $endsAt): void
    {
        if ($startsAt !== null && $endsAt !== null && $endsAt <= $startsAt) {
            throw new \InvalidArgumentException('Registration end date must be after registration start date.');
        }

        $this->registrationStartsAt = $startsAt;
        $this->registrationEndsAt = $endsAt;
    }

    /**
     * @return Collection<int, EventRegistration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function hasRegistrations(): bool
    {
        return !$this->registrations->isEmpty();
    }

    public function getRegisteredCount(): int
    {
        return $this->registrations
            ->filter(static fn(EventRegistration $registration): bool => $registration->consumesCapacity())
            ->count();
    }


    public function hasAvailableCapacity(): bool
    {
        if ($this->capacity === null) {
            return true;
        }

        return $this->getRegisteredCount() < $this->capacity;
    }

    public function hasActiveRegistrationForPerson(Person $person): bool
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getPerson() === $person && $registration->isActive()) {
                return true;
            }

            if (
                $person->getId() !== null
                && $registration->getPerson()->getId() === $person->getId()
                && $registration->isActive()
            ) {
                return true;
            }
        }

        return false;
    }

    private function addRegistration(EventRegistration $registration): void
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
        }
    }

    private function validateTitle(): void
    {
        if ($this->title === '') {
            throw new \InvalidArgumentException('Event title cannot be empty.');
        }
    }

    private function validateDates(): void
    {
        if ($this->endsAt <= $this->startsAt) {
            throw new \InvalidArgumentException('Event end date must be after start date.');
        }
    }

    public function isPublicRegistrationEnabled(): bool
    {
        return $this->publicRegistrationEnabled;
    }

    public function enablePublicRegistration(): void
    {
        $this->publicRegistrationEnabled = true;
    }

    public function disablePublicRegistration(): void
    {
        $this->publicRegistrationEnabled = false;
    }

    /**
     * Checks whether the given instant is inside the configured registration window.
     */
    public function isWithinRegistrationWindow(\DateTimeImmutable $now): bool
    {
        if ($this->registrationStartsAt !== null && $now < $this->registrationStartsAt) {
            return false;
        }

        if ($this->registrationEndsAt !== null && $now > $this->registrationEndsAt) {
            return false;
        }

        return true;
    }

}
