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


    public static function class(): string
    {
        return Club::class;
    }


    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(20),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): Club {
            $organization = $attributes['organization'] ?? null;

            if (!$organization instanceof Organization) {
                throw new \LogicException('Missing required "organization" attribute for ClubFactory.');
            }

            $name = $attributes['name'] ?? null;

            if (!\is_string($name) || $name === '') {
                throw new \LogicException('Missing required "name" attribute for ClubFactory.');
            }

            return Club::create(
                organization: $organization,
                name: $name,
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
