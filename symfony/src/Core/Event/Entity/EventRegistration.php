<?php

namespace App\Core\Event\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Core\Event\Enum\EventRegistrationStatus;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Dto\EventRegistration\EventRegistrationCreateDto;
use App\Dto\EventRegistration\EventRegistrationListDto;
use App\Entity\Membership;
use App\Entity\Person;
use App\State\EventRegistration\EventRegistrationCancelProcessor;
use App\State\EventRegistration\EventRegistrationCollectionProvider;
use App\State\EventRegistration\EventRegistrationCreateProcessor;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/events/{eventId}/registrations',
            uriVariables: [
                'eventId' => new Link(
                    toProperty: 'event',
                    fromClass: Event::class,
                    identifiers: ['publicId'],
                ),
            ],
            output: EventRegistrationListDto::class,
            provider: EventRegistrationCollectionProvider::class,
            parameters: [
                'status' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'status',
                ),
                'orderRequestedAt' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'requestedAt',
                ),
                'orderStatus' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'status',
                ),
                'orderPersonLastname' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'person.lastname',
                ),
            ],
        ),
        new Post(
            uriTemplate: '/events/{eventId}/registrations',
            uriVariables: [
                'eventId' => new Link(
                    toProperty: 'event',
                    fromClass: Event::class,
                    identifiers: ['publicId'],
                ),
            ],
            input: EventRegistrationCreateDto::class,
            output: EventRegistrationListDto::class,
            processor: EventRegistrationCreateProcessor::class,
        ),
        new Post(
            uriTemplate: '/event-registrations/{id}/cancel',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            status: 200,
            output: EventRegistrationListDto::class,
            read: true,
            deserialize: false,
            processor: EventRegistrationCancelProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]
#[ORM\Entity(repositoryClass: EventRegistrationRepository::class)]
#[ORM\Table(name: 'event_registration')]
class EventRegistration
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: TYPES::STRING, length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(name: 'event_id', nullable: false, onDelete: 'RESTRICT')]
    private Event $event;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(name: 'person_id', nullable: false, onDelete: 'RESTRICT')]
    private Person $person;

    #[ORM\ManyToOne(targetEntity: Membership::class)]
    #[ORM\JoinColumn(name: 'membership_id', nullable: true, onDelete: 'SET NULL')]
    private ?Membership $membership = null;

    #[ORM\Column(name: 'status', nullable: false, enumType: EventRegistrationStatus::class)]
    private EventRegistrationStatus $status;

    #[ORM\Column(name: 'requested_at', type: TYPES::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $requestedAt;

    #[ORM\Column(name: 'confirmed_at', type: TYPES::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(name: 'cancelled_at', type: TYPES::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(name: 'note', type: TYPES::TEXT, nullable: true)]
    private ?string $note = null;

    private function __construct(Event                   $event,
                                 Person                  $person,
                                 ?Membership             $membership,
                                 EventRegistrationStatus $status,
                                 \DateTimeImmutable      $requestedAt,
                                 ?\DateTimeImmutable     $confirmedAt)
    {
        $this->publicId = new Ulid();
        $this->event = $event;
        $event->addRegistration($this);

        $this->person = $person;
        $this->membership = $membership;
        $this->status = $status;
        $this->requestedAt = $requestedAt;
        $this->confirmedAt = $confirmedAt;

        $this->validateMembership();
        $this->validateStatusDates();
    }


    public static function register(Event $event, Person $person, ?Membership $membership = null, ?DateTimeImmutable $now = null): self
    {
        $now ??= new DateTimeImmutable();

        return new self(event: $event, person: $person, membership: $membership, status: EventRegistrationStatus::Registered,
            requestedAt: $now, confirmedAt: $now);
    }

    public static function waitlist(Event $event, Person $person, ?Membership $membership = null, ?DateTimeImmutable $now = null): self
    {
        $now ??= new DateTimeImmutable();

        return new self(event: $event, person: $person, membership: $membership, status: EventRegistrationStatus::Waitlisted,
            requestedAt: $now, confirmedAt: null);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function getMembership(): ?Membership
    {
        return $this->membership;
    }

    public function getStatus(): EventRegistrationStatus
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function consumesCapacity(): bool
    {
        return $this->status->consumesCapacity();
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function changeNote(?string $note): void
    {
        $note = $note !== null ? trim($note) : null;

        $this->note = $note !== '' ? $note : null;
    }


    public function promoteFromWaitlist(?\DateTimeImmutable $now = null): void
    {
        if ($this->status !== EventRegistrationStatus::Waitlisted) {
            throw new \LogicException('Only waitlisted registrations can be promoted.');
        }

        $now ??= new \DateTimeImmutable();

        $this->status = EventRegistrationStatus::Registered;
        $this->confirmedAt = $now;
        $this->cancelledAt = null;

        $this->validateStatusDates();
    }

    public function cancel(?\DateTimeImmutable $now = null): void
    {
        if ($this->status === EventRegistrationStatus::Cancelled) {
            return;
        }

        $now ??= new \DateTimeImmutable();

        $this->status = EventRegistrationStatus::Cancelled;
        $this->cancelledAt = $now;

        $this->validateStatusDates();
    }

    private function validateMembership(): void
    {
        if ($this->membership === null) {
            return;
        }

        if ($this->membership->getPerson() !== $this->person) {
            throw new \InvalidArgumentException('The membership must belong to the registered person.');
        }

        $eventClub = $this->event->getClub();

        if ($eventClub !== null && $this->membership->getClub() !== $eventClub) {
            throw new \InvalidArgumentException('The membership must belong to the event club.');
        }
    }

    private function validateStatusDates(): void
    {
        if ($this->status === EventRegistrationStatus::Registered) {
            if ($this->confirmedAt === null) {
                throw new \LogicException('A registered registration must have a confirmation date.');
            }

            if ($this->cancelledAt !== null) {
                throw new \LogicException('A registered registration cannot have a cancellation date.');
            }

            return;
        }

        if ($this->status === EventRegistrationStatus::Waitlisted) {
            if ($this->confirmedAt !== null) {
                throw new \LogicException('A waitlisted registration cannot have a confirmation date.');
            }

            if ($this->cancelledAt !== null) {
                throw new \LogicException('A waitlisted registration cannot have a cancellation date.');
            }

            return;
        }

        if ($this->status === EventRegistrationStatus::Cancelled) {
            if ($this->cancelledAt === null) {
                throw new \LogicException('A cancelled registration must have a cancellation date.');
            }
        }
    }

}
