<?php

namespace App\Factory;

use App\Entity\Organization;
use App\Entity\Person;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Person>
 */
final class PersonFactory extends PersistentObjectFactory
{

    public static function class(): string
    {
        return Person::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->email(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
        ];
    }


    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): Person {
            $organization = $attributes['organization'] ?? null;

            if (!$organization instanceof Organization) {
                throw new \LogicException('Missing required "organization" attribute for PersonFactory.');
            }

            $firstname = $attributes['firstname'] ?? null;

            if (!\is_string($firstname) || $firstname === '') {
                throw new \LogicException('Missing required "firstname" attribute for PersonFactory.');
            }

            $lastname = $attributes['lastname'] ?? null;

            if (!\is_string($lastname) || $lastname === '') {
                throw new \LogicException('Missing required "lastname" attribute for PersonFactory.');
            }

            $email = $attributes['email'] ;

            return Person::create(
                organization: $organization,
                firstname: $firstname,
                lastname: $lastname,
                email: $email
            );
        });
    }

    public function forOrganization(Organization $organization): self
    {
        return $this->with([
            'organization' => $organization,
        ]);
    }

}
