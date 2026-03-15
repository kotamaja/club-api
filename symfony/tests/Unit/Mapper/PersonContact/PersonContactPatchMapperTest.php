<?php

namespace App\Tests\Unit\Mapper\PersonContact;

use App\Dto\PersonContact\PersonContactPatchDto;
use App\Entity\PersonContact;
use App\Enum\RelationshipType;
use App\Mapper\CustomMapper\PersonContact\PersonContactPatchDtoToPersonContactMapper;
use PHPUnit\Framework\TestCase;

final class PersonContactPatchMapperTest extends TestCase
{
    public function testMapUpdatesOnlyProvidedFields(): void
    {
        $contact = new PersonContact();
        $contact->setType(RelationshipType::PARENT);
        $contact->setIsEmergencyContact(false);

        $dto = new PersonContactPatchDto();
        $dto->setIsEmergencyContact(true);

        $mapper = new PersonContactPatchDtoToPersonContactMapper();

        $result = $mapper->map($dto, $contact);

        $this->assertSame($contact, $result);
        $this->assertSame(RelationshipType::PARENT, $contact->getType());
        $this->assertTrue($contact->isEmergencyContact());
    }

    public function testMapDoesNothingWhenNoFieldIsProvided(): void
    {
        $contact = new PersonContact();
        $contact->setType(RelationshipType::LEGAL_GUARDIAN);
        $contact->setIsEmergencyContact(true);

        $dto = new PersonContactPatchDto();

        $mapper = new PersonContactPatchDtoToPersonContactMapper();

        $result = $mapper->map($dto, $contact);

        $this->assertSame($contact, $result);
        $this->assertSame(RelationshipType::LEGAL_GUARDIAN, $contact->getType());
        $this->assertTrue($contact->isEmergencyContact());
    }

    public function testMapCanUpdateAllFields(): void
    {
        $contact = new PersonContact();
        $contact->setType(RelationshipType::PARENT);
        $contact->setIsEmergencyContact(false);

        $dto = new PersonContactPatchDto();
        $dto->setType(RelationshipType::OTHER);
        $dto->setIsEmergencyContact(true);

        $mapper = new PersonContactPatchDtoToPersonContactMapper();

        $result = $mapper->map($dto, $contact);

        $this->assertSame($contact, $result);
        $this->assertSame(RelationshipType::OTHER, $contact->getType());
        $this->assertTrue($contact->isEmergencyContact());
    }
}
