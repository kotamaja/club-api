<?php

namespace App\Factory;

use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
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

    public function forClub(Club $club): self
    {
        return $this->with([
            'club' => $club,
        ]);
    }

    public function forPerson(Person $person): self
    {
        return $this->with([
            'person' => $person,
        ]);
    }

    public function endedAt(?\DateTimeImmutable $endedAt): self
    {
        return $this->with([
            'endedAt' => $endedAt,
        ]);
    }


    public function forClubWithGeneratedPerson(Club $club): self
    {
        return $this->with([
            'club' => $club,
            'person' => PersonFactory::new()
                ->forOrganization($club->getOrganization()),
        ]);
    }

    public function forPersonWithGeneratedClub(Person $person): self
    {
        return $this->with([
            'person' => $person,
            'club' => ClubFactory::new()
                ->forOrganization($person->getOrganization()),
        ]);
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): Membership {
            $person = $attributes['person'] ?? null;
            $club = $attributes['club'] ?? null;
            $joinedAt = $attributes['joinedAt'] ?? null;
            $endedAt = $attributes['endedAt'] ?? null;

            if (!$person instanceof Person) {
                throw new \LogicException('Missing required "person" attribute for MembershipFactory.');
            }

            if (!$club instanceof Club) {
                throw new \LogicException('Missing required "club" attribute for MembershipFactory.');
            }

            if (!$joinedAt instanceof \DateTimeImmutable) {
                throw new \LogicException('Missing required "joinedAt" attribute for MembershipFactory.');
            }

            $membership =  Membership::create(
                person: $person,
                club: $club,
                joinedAt: $joinedAt,
            );

            if ($endedAt instanceof \DateTimeImmutable) {
                $membership->setEndedAt($endedAt);
            }

            return $membership;
        });
    }
}
