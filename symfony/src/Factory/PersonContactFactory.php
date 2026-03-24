<?php

namespace App\Factory;

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
            'person' => PersonFactory::new(),
            'contactPerson' => PersonFactory::new(),
            'type' => RelationshipType::PARENT,
            'isEmergencyContact' => false,
        ];
    }
}
