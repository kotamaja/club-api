<?php

namespace App\State\ClubMembershipGroup;

use App\Entity\ClubMembershipGroup;
use App\State\AbstractDeleteProcessor;
use App\Write\ClubMembershipGroup\ClubMembershipGroupWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ClubMembershipGroupDeleteProcessor extends AbstractDeleteProcessor
{

    public function __construct(EntityManagerInterface                                    $em,
                                Security                                                  $security,
                                private readonly ClubMembershipGroupWriteServiceInterface $clubMembershipGroupWriteService)
    {
        parent::__construct($em, $security);
    }

    protected function entityClass(): string
    {
        return ClubMembershipGroup::class;
    }

    protected function deleteEntity(object $entity, array $context): void
    {
        \assert($entity instanceof ClubMembershipGroup);

        $this->clubMembershipGroupWriteService->delete($entity, $this->getCurrentConnectionUser());
    }

}
