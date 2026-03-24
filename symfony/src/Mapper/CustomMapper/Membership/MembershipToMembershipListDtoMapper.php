<?php

namespace App\Mapper\CustomMapper\Membership;

use App\Dto\Membership\MembershipListDto;
use App\Entity\Membership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: Membership::class, target: MembershipListDto::class)]
final class MembershipToMembershipListDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Membership) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', Membership::class, get_debug_type($source)));
        }


        $dto = $target instanceof MembershipListDto ? $target : new MembershipListDto();


        $person = $source->getPerson();
        $club = $source->getClub();

        if (null === $person) {
            throw new \LogicException('Membership.person must not be null when mapping to MembershipListDto.');
        }

        if (null === $club) {
            throw new \LogicException('Membership.club must not be null when mapping to MembershipListDto.');
        }

        $dto->id = $source->getPublicId();
        $dto->personId = $person->getPublicId();
        $target->personFirstName = $person->getFirstname();
        $target->personLastName = $person->getLastname();


        $dto->clubId = $club->getPublicId();
        $target->clubName = $club->getName();

        $dto->joinedAt = $source->getJoinedAt();
        $dto->endedAt = $source->getEndedAt();
        $dto->isActive = $source->isActive();

        return $dto;
    }
}
