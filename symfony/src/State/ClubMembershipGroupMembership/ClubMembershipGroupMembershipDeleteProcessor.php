<?php

namespace App\State\ClubMembershipGroupMembership;

use App\Entity\ClubMembershipGroupMembership;
use App\State\Util\AbstractDeleteProcessor;

class ClubMembershipGroupMembershipDeleteProcessor extends AbstractDeleteProcessor
{
    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof ClubMembershipGroupMembership) {
            throw new \LogicException('Expected ClubMembershipGroupMembership entity.');
        }


        return null;
    }


}
