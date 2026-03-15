<?php

namespace App\Mapper\CustomMapper\Club;

use App\Dto\Club\ClubItemDto;
use App\Entity\Club;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;


#[Maps(source: Club::class, target: ClubItemDto::class)]
final class ClubToClubItemDtoMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Club) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubItemDto ? $target : new ClubItemDto();

        $dto->id = $source->getPublicId();
        $dto->name = $source->getName();

        return $dto;
    }
}
