<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Contract\OrganizationScopedInterface;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use Doctrine\ORM\QueryBuilder;

final readonly class OrganizationScopeExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private CurrentOrganizationContext $currentOrganizationContext)
    {
    }

    public function applyToCollection(QueryBuilder                $queryBuilder,
                                      QueryNameGeneratorInterface $queryNameGenerator,
                                      string                      $resourceClass,
                                      ?Operation                  $operation = null,
                                      array                       $context = []): void
    {
        $this->addOrganizationScope($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(QueryBuilder                $queryBuilder,
                                QueryNameGeneratorInterface $queryNameGenerator,
                                string                      $resourceClass,
                                array                       $identifiers,
                                ?Operation                  $operation = null,
                                array                       $context = []): void
    {
        $this->addOrganizationScope($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function addOrganizationScope(QueryBuilder                $queryBuilder,
                                          QueryNameGeneratorInterface $queryNameGenerator,
                                          string                      $resourceClass): void
    {
        if (!is_subclass_of($resourceClass, OrganizationScopedInterface::class)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0] ?? null;

        if ($rootAlias === null) {
            return;
        }

        $parameterName = $queryNameGenerator->generateParameterName('current_organization');

        $queryBuilder
            ->andWhere(sprintf('%s.organization = :%s', $rootAlias, $parameterName))
            ->setParameter($parameterName, $this->currentOrganizationContext->getOrganization());
    }
}
