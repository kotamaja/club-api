<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupListDto;
use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipItemDto;
use App\Dto\Membership\MembershipListDto;
use App\Entity\ClubMembershipGroup;
use App\Entity\ClubMembershipGroupMembership;
use App\Entity\Membership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\MapperRegistryInterface;
use App\Mapper\Maps;

#[Maps(source: ClubMembershipGroupMembership::class, target: ClubMembershipGroupMembershipItemDto::class)]
class ClubMembershipGroupMembershipToItemDtoMapper implements CustomMapperInterface
{


    public function __construct(private readonly MapperRegistryInterface $mapperRegistry)
    {
    }


    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroupMembership) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubMembershipGroupMembershipItemDto ? $target : new ClubMembershipGroupMembershipItemDto();

        $dto->id = $source->getPublicId();

        $group = $source->getGroup();
        if (!$group instanceof ClubMembershipGroup) {
            throw new \LogicException('ClubMembershipGroupMembership.group must not be null when mapping to ClubMembershipGroupMembershipItemDto.');
        } else {
            $dto->clubMembershipGroup = $this->mapperRegistry->map($group, ClubMembershipGroupListDto::class);
        }

        $membership = $source->getMembership();
        if (!$membership instanceof Membership) {
            throw new \LogicException('ClubMembershipGroupMembership.membership must not be null when mapping to ClubMembershipGroupMembershipItemDto.');
        } else {
            $dto->membership = $this->mapperRegistry->map($membership, MembershipListDto::class);
        }

        $dto->notes = $source->getNotes();

        return $dto;
    }
}
