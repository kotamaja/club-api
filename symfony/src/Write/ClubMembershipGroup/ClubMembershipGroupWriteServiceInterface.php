<?php

namespace App\Write\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupCreateDto;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Entity\ClubMembershipGroup;
use App\Entity\ConnectionUser;

interface ClubMembershipGroupWriteServiceInterface
{
    public function create(ClubMembershipGroupCreateDto $input, ConnectionUser $actor): ClubMembershipGroup;

    public function patch(ClubMembershipGroupPatchDto $input, ClubMembershipGroup $clubMembershipGroup, ConnectionUser $actor): ClubMembershipGroupPatchResult;

    public function delete(ClubMembershipGroup $clubMembershipGroup, ConnectionUser $actor): void;
}
