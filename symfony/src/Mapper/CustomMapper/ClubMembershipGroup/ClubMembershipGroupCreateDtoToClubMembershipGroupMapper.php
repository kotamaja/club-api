<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupCreateDto;
use App\Entity\ClubMembershipGroup;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: ClubMembershipGroupCreateDto::class, target: ClubMembershipGroup::class)]
class ClubMembershipGroupCreateDtoToClubMembershipGroupMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroupCreateDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $personRelationship = $target instanceof ClubMembershipGroup ? $target : new ClubMembershipGroup();

        $personRelationship->changeName($source->name);
        $personRelationship->changeDescription($source->description);

        return $personRelationship;
    }
}
