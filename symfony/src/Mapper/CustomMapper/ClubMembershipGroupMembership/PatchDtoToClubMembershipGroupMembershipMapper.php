<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipPatchDto;
use App\Entity\ClubMembershipGroupMembership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;


#[Maps(source: ClubMembershipGroupMembershipPatchDto::class, target: ClubMembershipGroupMembership::class)]
class PatchDtoToClubMembershipGroupMembershipMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {

        if (!$source instanceof ClubMembershipGroupMembershipPatchDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $personRelationship = $target instanceof ClubMembershipGroupMembership ? $target : new ClubMembershipGroupMembership();


        if ($source->isNotesProvided()) {
            $personRelationship->setNotes($source->getNotes());
        }

        return $personRelationship;
    }
}
