<?php

namespace App\State\Membership;

use App\Entity\Membership;
use App\State\AbstractDeleteProcessor;
use App\Write\Membership\MembershipWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class MembershipDeleteProcessor extends AbstractDeleteProcessor
{

    public function __construct(EntityManagerInterface                           $em,
                                Security                                         $security,
                                private readonly MembershipWriteServiceInterface $membershipWriteService)
    {
        parent::__construct($em, $security);
    }

    protected function entityClass(): string
    {
        return Membership::class;
    }

    protected function deleteEntity(object $entity, array $context): void
    {
        \assert($entity instanceof Membership);

        $this->membershipWriteService->delete($entity, $this->getCurrentConnectionUser());
    }

}
