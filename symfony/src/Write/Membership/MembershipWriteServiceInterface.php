<?php

namespace App\Write\Membership;

use App\Dto\Membership\MembershipCreateDto;
use App\Dto\Membership\MembershipPatchDto;
use App\Entity\ConnectionUser;
use App\Entity\Membership;

interface MembershipWriteServiceInterface
{
    public function create(MembershipCreateDto $input, ConnectionUser $actor): Membership;

    public function patch(MembershipPatchDto $input, Membership $membership, ConnectionUser $actor): MembershipPatchResult;

    public function delete(Membership $membership, ConnectionUser $actor): void;
}
