<?php

namespace App\Mapper\CustomMapper\Event;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Enum\EventRegistrationStatus;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Dto\Event\EventItemDto;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;
use App\Security\OrganizationContext\CurrentOrganizationContext;

#[Maps(source: Event::class, target: EventItemDto::class)]
final readonly class EventToEventItemDtoMapper implements CustomMapperInterface
{
    public function __construct(
        private CurrentOrganizationContext $currentOrganizationContext,
        private EventRegistrationRepository $eventRegistrationRepository,
    ) {
    }

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Event) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof EventItemDto ? $target : new EventItemDto();

        $dto->id = $source->getPublicId();

        $dto->title = $source->getTitle();
        $dto->description = $source->getDescription();
        $dto->location = $source->getLocation();

        $dto->type = $source->getType()->value;
        $dto->status = $source->getStatus()->value;

        $club = $source->getClub();
        $dto->clubId = $club?->getPublicId();
        $dto->clubName = $club?->getName();

        $dto->startsAt = $source->getStartsAt();
        $dto->endsAt = $source->getEndsAt();
        $dto->timezone = $source->getTimezone();
        $dto->allDay = $source->isAllDay();

        $dto->capacity = $source->getCapacity();
        $dto->registeredCount = $source->getRegisteredCount();
        $dto->waitlistedCount = $this->getWaitlistedCount($source);
        $dto->waitlistEnabled = $source->isWaitlistEnabled();

        $dto->publicRegistrationEnabled = $source->isPublicRegistrationEnabled();
        $dto->registrationStartsAt = $source->getRegistrationStartsAt();
        $dto->registrationEndsAt = $source->getRegistrationEndsAt();

        $dto->myRegistrationStatus = $this->resolveMyRegistrationStatus($source);

        return $dto;
    }

    private function getWaitlistedCount(Event $event): int
    {
        return $event->getRegistrations()
            ->filter(static fn (EventRegistration $registration): bool => $registration->getStatus() === EventRegistrationStatus::Waitlisted)
            ->count();
    }

    private function resolveMyRegistrationStatus(Event $event): ?string
    {
        $person = $this->currentOrganizationContext->getPerson();

        if ($person === null) {
            return null;
        }

        return $this->eventRegistrationRepository
            ->findActiveRegistrationForPerson($event, $person)
            ?->getStatus()
            ->value;
    }
}
