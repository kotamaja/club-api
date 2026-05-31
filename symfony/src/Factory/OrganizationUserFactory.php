<?php

namespace App\Factory;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\OrganizationUser;
use App\Entity\Person;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<OrganizationUser>
 */
final class OrganizationUserFactory extends PersistentObjectFactory
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
        return OrganizationUser::class;
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
            'connectionUser' => ConnectionUserFactory::new(),
            'organization' => OrganizationFactory::new(),
            'roles' => [],
            'person' => null,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(OrganizationUser $organizationUser): void {})
        ;
    }

    public function forConnectionUser(ConnectionUser $connectionUser): static
    {
        return $this->with([
            'connectionUser' => $connectionUser,
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->with([
            'organization' => $organization,
        ]);
    }

    public function withRoles(array $roles): static
    {
        return $this->with([
            'roles' => $roles,
        ]);
    }

    public function withPerson(?Person $person): static
    {
        return $this->with([
            'person' => $person,
        ]);
    }
}
