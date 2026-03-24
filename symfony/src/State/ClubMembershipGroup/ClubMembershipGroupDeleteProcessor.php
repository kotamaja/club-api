<?php

namespace App\State\ClubMembershipGroup;

use App\Entity\ClubMembershipGroup;
use App\Entity\ClubMembershipGroupMembership;
use App\State\Util\AbstractDeleteProcessor;

class ClubMembershipGroupDeleteProcessor extends AbstractDeleteProcessor
{
    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof ClubMembershipGroup) {
            throw new \LogicException('Expected ClubMembershipGroup entity.');
        }

        // ClubMembershipGroup should be empty before removal
        $count = $this->em->getRepository(ClubMembershipGroupMembership::class)->count(['group' => $entity]);
        if ($count > 0) {
            return 'Cannot delete group: member still exist.';
        }


        return null;
    }


}
