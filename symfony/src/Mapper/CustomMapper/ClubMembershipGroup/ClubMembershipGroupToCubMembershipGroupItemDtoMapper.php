<?php

namespace App\Mapper\CustomMapper\ClubMembershipGroup;

use App\Dto\Club\ClubListDto;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupItemDto;
use App\Entity\ClubMembershipGroup;
use App\Mapper\CustomMapperInterface;
use App\Mapper\MapperRegistryInterface;
use App\Mapper\Maps;


#[Maps(source: ClubMembershipGroup::class, target: ClubMembershipGroupItemDto::class)]
class ClubMembershipGroupToCubMembershipGroupItemDtoMapper implements CustomMapperInterface
{

    public function __construct(private readonly MapperRegistryInterface $mapperRegistry)
    {
    }

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubMembershipGroup) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubMembershipGroupItemDto ? $target : new ClubMembershipGroupItemDto();

        $dto->id = $source->getPublicId();
        $dto->name = $source->getName();
        $dto->description = $source->getDescription();
        $dto->membershipCount = $source->getClubMembershipGroupMemberships()->count();

        $club = $source->getClub();
        if (null === $club) {
            throw new \LogicException('MembClubMembershipGrouper.club must not be null when mapping to ClubMembershipGroupItemDto.');
        } else {
            $dto->club = $this->mapperRegistry->map($club, ClubListDto::class);
        }


        return $dto;
    }
}

