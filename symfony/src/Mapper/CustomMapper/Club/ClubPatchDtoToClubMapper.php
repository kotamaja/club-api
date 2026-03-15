<?php

namespace App\Mapper\CustomMapper\Club;

use App\Dto\Club\ClubPatchDto;
use App\Entity\Club;
use App\Mapper\CustomMapperInterface;
use App\Mapper\InvalidInputException;
use App\Mapper\Maps;
use RuntimeException;

#[Maps(source: ClubPatchDto::class, target: Club::class)]
final class ClubPatchDtoToClubMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {

        if (!$source instanceof ClubPatchDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $club = $target instanceof Club ? $target : new Club();

        if ($source->isNameProvided()) {
            $name = $source->getName();
            if ($name === null || trim($name) === '') {
                throw new InvalidInputException('name cannot be blank', 'name');
            }
            $club->setName($name);
        }

        return $club;
    }
}
