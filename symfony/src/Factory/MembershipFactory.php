<?php

namespace App\Factory;

use App\Entity\Club;
use App\Entity\Membership;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Membership>
 */
final class MembershipFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Membership::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'joinedAt' => new \DateTimeImmutable(),
            'endedAt' => null,
        ];
    }

    public static function forClub(Club $club): self
    {
        return self::new([
            'club' => $club,
            'person' => PersonFactory::new([
                'organization' => $club->getOrganization(),
            ]),
            'joinedAt' => new \DateTimeImmutable(),
        ]);
    }

    protected function initialize(): static
    {
        return $this
            ->instantiateWith(function (array $attributes): Membership {
                return new Membership(
                    $attributes['person'],
                    $attributes['club'],
                    $attributes['joinedAt'],
                );
            });
    }
}
