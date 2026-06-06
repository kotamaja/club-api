<?php

namespace App\Factory;

use App\Entity\Person;
use App\Entity\PersonContact;
use App\Enum\RelationshipType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PersonContact>
 */
final class PersonContactFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return PersonContact::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'type' => RelationshipType::PARENT,
            'isEmergencyContact' => false,
        ];
    }

    public function forPerson(Person $person): self
    {
        return $this->with([
            'person' => $person,
        ]);
    }

    public function forContactPerson(Person $person): self
    {
        return $this->with([
            'contactPerson' => $person,
        ]);
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): PersonContact {
            $person = $attributes['person'] ?? null;
            $contactPerson = $attributes['contactPerson'] ?? null;
            $type = $attributes['type'] ?? null;
            $isEmergencyContact = $attributes['isEmergencyContact'] ?? null;

            if (!$person instanceof Person) {
                throw new \LogicException('Missing required "person" attribute for PersonContact.');
            }

            if (!$contactPerson instanceof Person) {
                throw new \LogicException('Missing required "contactPerson" attribute for PersonContact.');
            }

            $personContact =  PersonContact::create(
                person: $person,
                contactPerson: $contactPerson,
                type: $type,
                isEmergencyContact: $isEmergencyContact,
            );


            return $personContact;
        });
    }
}
