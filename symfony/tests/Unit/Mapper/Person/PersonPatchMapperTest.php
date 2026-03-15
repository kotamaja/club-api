<?php

namespace App\Tests\Unit\Mapper\Person;

use App\Dto\Person\PersonPatchDto;
use App\Entity\Person;
use App\Mapper\CustomMapper\Person\PersonPatchDtoToPersonMapper;
use PHPUnit\Framework\TestCase;

final class PersonPatchMapperTest extends TestCase
{
    public function testMapUpdatesOnlyProvidedFields(): void
    {
        $person = new Person();
        $person->setFirstname('Yves');
        $person->setLastname('Dupont');
        $person->setEmail('yves.dupont@example.com');

        $dto = new PersonPatchDto();
        $dto->setLastname('Durand');

        $mapper = new PersonPatchDtoToPersonMapper();

        $result = $mapper->map($dto, $person);

        $this->assertSame($person, $result);
        $this->assertSame('Yves', $person->getFirstname());
        $this->assertSame('Durand', $person->getLastname());
        $this->assertSame('yves.dupont@example.com', $person->getEmail());
    }

    public function testMapDoesNothingWhenNoFieldIsProvided(): void
    {
        $person = new Person();
        $person->setFirstname('Yves');
        $person->setLastname('Dupont');
        $person->setEmail('yves.dupont@example.com');

        $dto = new PersonPatchDto();

        $mapper = new PersonPatchDtoToPersonMapper();

        $result = $mapper->map($dto, $person);

        $this->assertSame($person, $result);
        $this->assertSame('Yves', $person->getFirstname());
        $this->assertSame('Dupont', $person->getLastname());
        $this->assertSame('yves.dupont@example.com', $person->getEmail());
    }

    public function testMapUpdatesAllProvidedFields(): void
    {
        $person = new Person();
        $person->setFirstname('Yves');
        $person->setLastname('Dupont');
        $person->setEmail('yves.dupont@example.com');

        $dto = new PersonPatchDto();
        $dto->setFirstname('Anne');
        $dto->setLastname('Martin');
        $dto->setEmail('anne.martin@example.com');

        $mapper = new PersonPatchDtoToPersonMapper();

        $result = $mapper->map($dto, $person);

        $this->assertSame($person, $result);
        $this->assertSame('Anne', $person->getFirstname());
        $this->assertSame('Martin', $person->getLastname());
        $this->assertSame('anne.martin@example.com', $person->getEmail());
    }
}
