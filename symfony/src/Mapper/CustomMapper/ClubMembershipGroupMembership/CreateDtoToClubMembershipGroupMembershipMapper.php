<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipCreateDto;
use App\Entity\ClubMembershipGroupMembership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: ClubMembershipGroupMembershipCreateDto::class, target: ClubMembershipGroupMembership::class)]
class CreateDtoToClubMembershipGroupMembershipMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroupMembershipCreateDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $groupMembership = $target instanceof ClubMembershipGroupMembership ? $target : new ClubMembershipGroupMembership();

        $groupMembership->setNotes($source->notes);

        return $groupMembership;
    }
}

