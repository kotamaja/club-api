<?php

namespace App\Tests\Unit\Mapper\PersonContact;

use App\Dto\PersonContact\PersonContactViewDto;
use App\Entity\Person;
use App\Entity\PersonContact;
use App\Enum\RelationshipType;
use App\Mapper\CustomMapper\PersonContact\PersonContactToPersonContactViewDtoMapper;
use PHPUnit\Framework\TestCase;

final class PersonContactToViewDtoMapperTest extends TestCase
{
    public function testMap(): void
    {
        $person = new Person();
        $contactPerson = new Person();

        $contact = new PersonContact();
        $contact->setPerson($person);
        $contact->setContactPerson($contactPerson);
        $contact->setType(RelationshipType::LEGAL_GUARDIAN);
        $contact->setIsEmergencyContact(true);

        $mapper = new PersonContactToPersonContactViewDtoMapper();

        /** @var PersonContactViewDto $dto */
        $dto = $mapper->map($contact, PersonContactViewDto::class);

        $this->assertSame($contact->getPublicId(), $dto->id);
        $this->assertSame($person->getPublicId(), $dto->personId);
        $this->assertSame($contactPerson->getPublicId(), $dto->contactPersonId);
        $this->assertSame(RelationshipType::LEGAL_GUARDIAN, $dto->type);
        $this->assertTrue($dto->isEmergencyContact);
    }
}
