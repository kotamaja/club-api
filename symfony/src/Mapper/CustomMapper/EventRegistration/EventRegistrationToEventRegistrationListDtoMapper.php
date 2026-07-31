<?php

namespace App\Mapper\CustomMapper\EventRegistration;

use App\Core\Event\Entity\EventRegistration;
use App\Dto\EventRegistration\EventRegistrationListDto;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: EventRegistration::class, target: EventRegistrationListDto::class)]
final class EventRegistrationToEventRegistrationListDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof EventRegistration) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof EventRegistrationListDto ? $target : new EventRegistrationListDto();

        $event = $source->getEvent();
        $person = $source->getPerson();
        $membership = $source->getMembership();

        $dto->id = $source->getPublicId();

        $dto->eventId = $event->getPublicId();
        $dto->eventTitle = $event->getTitle();

        $dto->personId = $person->getPublicId();
        $dto->personFirstname = $person->getFirstname();
        $dto->personLastname = $person->getLastname();

        $dto->membershipId = $membership?->getPublicId();

        $dto->status = $source->getStatus()->value;
        $dto->requestedAt = $source->getRequestedAt();
        $dto->cancelledAt = $source->getCancelledAt();

        $dto->note = $source->getNote();

        return $dto;
    }
}
