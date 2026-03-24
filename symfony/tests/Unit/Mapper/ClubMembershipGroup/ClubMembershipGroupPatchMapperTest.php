<?php

namespace App\Tests\Unit\Mapper\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Dto\PersonContact\PersonContactPatchDto;
use App\Entity\ClubMembershipGroup;
use App\Entity\PersonContact;
use App\Enum\RelationshipType;
use App\Mapper\CustomMapper\ClubMembershipGroup\ClubMembershipGroupPatchDtoToClubMembershipGroupMapper;
use App\Mapper\CustomMapper\PersonContact\PersonContactPatchDtoToPersonContactMapper;
use PHPUnit\Framework\TestCase;

class ClubMembershipGroupPatchMapperTest extends TestCase
{
    public function testMapUpdatesAllProvidedFields(): void
    {


        $clubMembershipGroup = new ClubMemberShipGroup();
        $clubMembershipGroup->setName("Test");
        $clubMembershipGroup->setDescription("Test Description");


        $dto=new ClubMembershipGroupPatchDto();
        $dto->setName("New Name");
        $dto->setDescription("New Description");


        $mapper = new ClubMembershipGroupPatchDtoToClubMembershipGroupMapper();

        $result = $mapper->map($dto, $clubMembershipGroup);

        $this->assertSame($clubMembershipGroup, $result);
        $this->assertSame("New Name", $result->getName());
        $this->assertSame( "New Description", $result->getDescription());
    }

    public function testMapDoesNothingWhenNoFieldIsProvided(): void
    {
        $clubMembershipGroup = new ClubMemberShipGroup();
        $clubMembershipGroup->setName("Test");
        $clubMembershipGroup->setDescription("Test Description");


        $dto=new ClubMembershipGroupPatchDto();


        $mapper = new ClubMembershipGroupPatchDtoToClubMembershipGroupMapper();

        $result = $mapper->map($dto, $clubMembershipGroup);

        $this->assertSame($clubMembershipGroup, $result);
        $this->assertSame("Test", $result->getName());
        $this->assertSame( "Test Description", $result->getDescription());
    }

}
