<?php

namespace App\Mapper\CustomMapper\EventRegistration;

use App\Core\Event\Entity\EventRegistration;
use App\Dto\EventRegistration\PublicEventRegistrationItemDto;

use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: EventRegistration::class, target: PublicEventRegistrationItemDto::class)]
final class EventRegistrationToPublicEventRegistrationItemDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof EventRegistration) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof PublicEventRegistrationItemDto ? $target : new PublicEventRegistrationItemDto();

        $event = $source->getEvent();

        $dto->id = $source->getPublicId();
        $dto->eventId = $event->getPublicId();
        $dto->eventTitle = $event->getTitle();
        $dto->status = $source->getStatus()->value;
        $dto->requestedAt = $source->getRequestedAt();
        $dto->note = $source->getNote();

        return $dto;
    }
}
