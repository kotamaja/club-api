<?php

namespace App\State\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipPatchDto;
use App\State\Util\AbstractPatchProcessor;

class ClubMembershipGroupMembershipPatchProcessor extends AbstractPatchProcessor
{

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubMembershipGroupMembershipPatchDto) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s, got %s.',
                ClubMembershipGroupMembershipPatchDto::class,
                get_debug_type($data)
            ));
        }
    }


}
