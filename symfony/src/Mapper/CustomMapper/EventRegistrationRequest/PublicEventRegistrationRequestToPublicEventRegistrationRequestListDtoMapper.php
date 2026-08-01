<?php

namespace App\Mapper\CustomMapper\EventRegistrationRequest;

use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestListDto;
use App\Entity\OrganizationUser;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PublicEventRegistrationRequest::class, target: PublicEventRegistrationRequestListDto::class)]
final class PublicEventRegistrationRequestToPublicEventRegistrationRequestListDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof PublicEventRegistrationRequest) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof PublicEventRegistrationRequestListDto ? $target : new PublicEventRegistrationRequestListDto();

        $event = $source->getEvent();
        $club = $event->getClub();

        if ($club === null) {
            throw new \LogicException('A public event registration request must be linked to a club event.');
        }

        $reviewedBy = $source->getReviewedBy();

        $dto->id = $source->getPublicId();
        $dto->eventId = $event->getPublicId();
        $dto->eventTitle = $event->getTitle();
        $dto->clubId = $club->getPublicId();
        $dto->clubName = $club->getName();
        $dto->firstname = $source->getFirstname();
        $dto->lastname = $source->getLastname();
        $dto->email = $source->getEmail();
        $dto->status = $source->getStatus()->value;
        $dto->requestedAt = $source->getRequestedAt();
        $dto->reviewedAt = $source->getReviewedAt();
        $dto->reviewedById = $reviewedBy?->getPublicId();
        $dto->reviewedByDisplayName = $this->getReviewedByDisplayName($reviewedBy);

        return $dto;
    }

    private function getReviewedByDisplayName(?OrganizationUser $reviewedBy): ?string
    {
        if (!$reviewedBy instanceof OrganizationUser) {
            return null;
        }

        $person = $reviewedBy->getPerson();

        if ($person !== null) {
            return trim($person->getFirstname() . ' ' . $person->getLastname());
        }

        return $reviewedBy->getConnectionUser()->getEmail();
    }
}
