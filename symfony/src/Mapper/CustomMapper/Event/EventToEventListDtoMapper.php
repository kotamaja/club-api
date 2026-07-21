<?php

namespace App\Mapper\CustomMapper\Event;

use App\Core\Event\Entity\Event;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Dto\Event\EventListDto;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;
use App\Security\OrganizationContext\CurrentOrganizationContext;

#[Maps(source: Event::class, target: EventListDto::class)]
final readonly class EventToEventListDtoMapper implements CustomMapperInterface
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

        $dto = $target instanceof EventListDto ? $target : new EventListDto();

        $dto->id = $source->getPublicId();
        $dto->title = $source->getTitle();
        $dto->type = $source->getType()->value;
        $dto->status = $source->getStatus()->value;

        $club = $source->getClub();
        $dto->clubId = $club?->getPublicId();
        $dto->clubName = $club?->getName();

        $dto->location = $source->getLocation();

        $dto->startsAt = $source->getStartsAt();
        $dto->endsAt = $source->getEndsAt();
        $dto->timezone = $source->getTimezone();
        $dto->allDay = $source->isAllDay();

        $dto->capacity = $source->getCapacity();
        $dto->registeredCount = $source->getRegisteredCount();
        $dto->waitlistEnabled = $source->isWaitlistEnabled();
        $dto->publicRegistrationEnabled = $source->isPublicRegistrationEnabled();

        $dto->myRegistrationStatus = $this->resolveMyRegistrationStatus($source);

        return $dto;
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
