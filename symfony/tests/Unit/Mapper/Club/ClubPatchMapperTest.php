<?php

namespace App\Tests\Unit\Mapper\Club;

use App\Dto\Club\ClubPatchDto;
use App\Entity\Club;
use App\Mapper\CustomMapper\Club\ClubPatchDtoToClubMapper;
use PHPUnit\Framework\TestCase;

final class ClubPatchMapperTest extends TestCase
{
    public function testMapUpdatesNameWhenProvided(): void
    {
        $club = new Club();
        $club->setName('Old Club Name');

        $dto = new ClubPatchDto();
        $dto->setName('New Club Name');

        $mapper = new ClubPatchDtoToClubMapper();

        $result = $mapper->map($dto, $club);

        $this->assertSame($club, $result);
        $this->assertSame('New Club Name', $club->getName());
    }

    public function testMapDoesNotChangeNameWhenNotProvided(): void
    {
        $club = new Club();
        $club->setName('Original Club Name');

        $dto = new ClubPatchDto();

        $mapper = new ClubPatchDtoToClubMapper();

        $result = $mapper->map($dto, $club);

        $this->assertSame($club, $result);
        $this->assertSame('Original Club Name', $club->getName());
    }
}
