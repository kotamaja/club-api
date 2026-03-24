<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipListDto;
use App\Entity\ClubMembershipGroupMembership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: ClubMembershipGroupMembership::class, target: ClubMembershipGroupMembershipListDto::class)]
class ClubMembershipGroupMembershipToListDtoMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroupMembership) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubMembershipGroupMembershipListDto ? $target : new ClubMembershipGroupMembershipListDto();

        $dto->id = $source->getPublicId();

        $dto->clubMembershipGroupId = $source->getGroup()->getPublicId();
        $dto->clubMembershipGroupName = $source->getGroup()->getName();

        $dto->membershipId = $source->getMembership()->getPublicId();

        $dto->personFirstname = $source->getMembership()->getPerson()->getFirstname();
        $dto->personLastname = $source->getMembership()->getPerson()->getLastname();

        $dto->notes = $source->getNotes();

        return $dto;
    }
}





