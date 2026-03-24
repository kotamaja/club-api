<?php

namespace App\Mapper\CustomMapper\Membership;

use App\Dto\Club\ClubListDto;
use App\Dto\Membership\MembershipItemDto;
use App\Dto\Person\PersonListDto;
use App\Entity\Membership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\MapperRegistryInterface;
use App\Mapper\Maps;

#[Maps(source: Membership::class, target: MembershipItemDto::class)]
final class MembershipToMembershipItemDtoMapper implements CustomMapperInterface
{
    public function __construct(private readonly MapperRegistryInterface $mapperRegistry)
    {
    }

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Membership) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', Membership::class, get_debug_type($source)));
        }


        $dto = $target instanceof MembershipItemDto ? $target : new MembershipItemDto();

        $person = $source->getPerson();
        if (null === $person) {
            throw new \LogicException('Membership.person must not be null when mapping to MembershipItemDto.');
        } else {
            $dto->person = $this->mapperRegistry->map($person, PersonListDto::class);
        }


        $club = $source->getClub();
        if (null === $club) {
            throw new \LogicException('Membership.club must not be null when mapping to MembershipItemDto.');
        } else {
            $dto->club = $this->mapperRegistry->map($club, ClubListDto::class);
        }

        $dto->id = $source->getPublicId();
        $dto->joinedAt = $source->getJoinedAt();
        $dto->endedAt = $source->getEndedAt();
        $dto->isActive = $source->isActive();

        return $dto;
    }
}
