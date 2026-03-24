<?php

namespace App\Factory;

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
            'person' => PersonFactory::new(),
            'club' => ClubFactory::new(),
            'joinedAt' => new \DateTimeImmutable(),
            'endedAt' => null,
        ];
    }
}
