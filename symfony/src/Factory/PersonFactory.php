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
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Person::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->email(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
        ];
    }



    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->instantiateWith(function (array $attributes): Person {
                if (!isset($attributes['organization']) || !$attributes['organization'] instanceof Organization) {
                    throw new \LogicException('Missing required "organization" attribute for PersonFactory.');
                }

                return new Person(
                    $attributes['firstname'],
                    $attributes['lastname'],
                    $attributes['organization'],
                );
            });
    }
}
