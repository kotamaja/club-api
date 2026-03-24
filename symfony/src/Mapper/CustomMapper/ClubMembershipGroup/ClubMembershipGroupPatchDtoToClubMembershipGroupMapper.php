<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Entity\ClubMembershipGroup;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: ClubMembershipGroupPatchDto::class, target: ClubMembershipGroup::class)]
class ClubMembershipGroupPatchDtoToClubMembershipGroupMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroupPatchDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', ClubMembershipGroupPatchDto::class, get_debug_type($source)));
        }

        $clubMembershipGroup = $target instanceof ClubMembershipGroup ? $target : new ClubMembershipGroup();

        if ($source->isNameProvided()) {
            $clubMembershipGroup->setName($source->getName());
        }

        if ($source->isDescriptionProvided()) {
            $clubMembershipGroup->setDescription($source->getDescription());
        }

        return $clubMembershipGroup;
    }
}
