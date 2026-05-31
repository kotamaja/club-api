<?php

namespace App\Factory;

use App\Entity\Club;
use App\Entity\Organization;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Club>
 */
final class ClubFactory extends PersistentObjectFactory
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
        return Club::class;
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
            'name' => self::faker()->text(20),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->instantiateWith(function (array $attributes): Club {
                if (!isset($attributes['organization']) || !$attributes['organization'] instanceof Organization) {
                    throw new \LogicException('Missing required "organization" attribute for ClubFactory.');
                }

                return new Club(
                    $attributes['name'],
                    $attributes['organization'],
                );
            });
    }
}
