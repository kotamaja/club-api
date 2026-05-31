<?php

namespace App\Security\OrganizationContext;

use App\Entity\Contract\OrganizationScopedInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class OrganizationScopeGuard
{
    public function __construct(private CurrentOrganizationContext $currentOrganizationContext)
    {
    }

    public function assertBelongsToCurrentOrganization(OrganizationScopedInterface $entity): void
    {
        if ($entity->getOrganization() !== $this->currentOrganizationContext->getOrganization()) {
            throw new NotFoundHttpException();
        }
    }
}
