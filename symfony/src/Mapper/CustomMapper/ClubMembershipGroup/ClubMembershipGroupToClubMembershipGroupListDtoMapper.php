<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupListDto;
use App\Entity\ClubMembershipGroup;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: ClubMembershipGroup::class, target: ClubMembershipGroupListDto::class)]
class ClubMembershipGroupToClubMembershipGroupListDtoMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroup) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubMembershipGroupListDto ? $target : new ClubMembershipGroupListDto();

        $dto->id = $source->getPublicId();
        $dto->name = $source->getName();

        $club = $source->getClub();
        $dto->clubId = $club->getId();
        $dto->clubName = $club->getName();

        return $dto;
    }
}


