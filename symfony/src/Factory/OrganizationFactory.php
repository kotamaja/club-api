<?php

namespace App\Factory;

use App\Core\Enum\ServicePlan;
use App\Entity\Organization;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Organization>
 */
final class OrganizationFactory extends PersistentObjectFactory
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
        return Organization::class;
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
            'name' => self::faker()->unique()->company(),
            'slug' => self::faker()->unique()->slug(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Organization $organization): void {})
        ;
    }

    public function withNameAndSlug(string $name, string $slug): static
    {
        return $this->with([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    public function withServicePlan(ServicePlan $servicePlan): static {
        return $this->with([
            'servicePlan' => $servicePlan,
        ]);
    }
}
