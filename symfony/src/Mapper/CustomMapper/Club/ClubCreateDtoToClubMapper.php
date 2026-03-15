<?php


namespace App\Mapper\CustomMapper\Club;


use App\Dto\Club\ClubCreateDto;
use App\Entity\Club;
use App\Mapper\CustomMapperInterface;
use App\Mapper\InvalidInputException;
use App\Mapper\Maps;


#[Maps(source: ClubCreateDto::class, target: Club::class)]
final class ClubCreateDtoToClubMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof ClubCreateDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $club = $target instanceof Club ? $target : new Club();

        if (trim($source->name) === '') {
            throw new InvalidInputException('name cannot be blank', 'name');
        }
        $club->setName($source->name);

        return $club;

    }
}
