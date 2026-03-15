<?php


namespace App\Mapper\CustomMapper\Club;

use App\Dto\Club\ClubListDto;
use App\Entity\Club;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;


#[Maps(source: Club::class, target: ClubListDto::class)]

final class ClubToClubListDtoMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Club) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubListDto ? $target : new ClubListDto();

        $dto->id = $source->getPublicId();
        $dto->name = $source->getName();

        return $dto;
    }
}
