<?php

namespace App\State\Club;

use App\Entity\Club;
use App\State\Util\AbstractDeleteProcessor;
use Doctrine\ORM\EntityManagerInterface;

final class ClubDeleteProcessor extends AbstractDeleteProcessor
{
    public function __construct(
        EntityManagerInterface $em,
    ) {
        parent::__construct($em);
    }

    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof Club) {
            throw new \LogicException('Expected Club entity.');
        }

        if ($entity->getMemberships()->count() > 0) {
            return 'Cannot delete club: memberships still exist.';
        }

        if ($entity->getClubMembershipGroups()->count() > 0) {
            return 'Cannot delete club: clubMembershipGroups still exist.';
        }

//        if ($entity->getInterclubMembershipGroups()->count() > 0) {
//            return 'Cannot delete club: interclubMembershipGroups still exist.';
//        }

        return null;
    }

    protected function foreignKeyConstraintViolationMessage(object $entity, array $context): ?string
    {
        return 'This club cannot be deleted because it is still used by other resources.';
    }
}
