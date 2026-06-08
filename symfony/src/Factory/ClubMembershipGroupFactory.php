<?php

namespace App\Factory;

use App\Entity\Club;
use App\Entity\ClubMembershipGroup;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

class ClubMembershipGroupFactory extends PersistentObjectFactory
{


    public static function class(): string
    {
        return ClubMembershipGroup::class;
    }


    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(180),
            'description' => self::faker()->text(150),
        ];
    }

    public function forClub(Club $club): self
    {
        return $this->with([
            'club' => $club,
        ]);
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): ClubMembershipGroup {
            $club = $attributes['club'] ?? null;
            $name = $attributes['name'] ?? null;
            $description = $attributes['description'] ?? null;

            if (!$club instanceof Club) {
                throw new \LogicException('Missing required "club" attribute for ClubMembershipGroup.');
            }

            $group =  ClubMembershipGroup::create(
                club: $club,
                name: $name,
                description: $description,
            );


            return $group;
        });
    }
}
