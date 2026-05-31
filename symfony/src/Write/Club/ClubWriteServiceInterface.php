<?php

namespace App\Write\Club;

use App\Dto\Club\ClubCreateDto;
use App\Dto\Club\ClubPatchDto;
use App\Entity\Club;
use App\Entity\ConnectionUser;

interface ClubWriteServiceInterface
{
    public function create(ClubCreateDto $input, ConnectionUser $actor ): Club;

    public function patch(ClubPatchDto $input, Club $club,  ConnectionUser $actor): ClubPatchResult;

    public function delete(Club $club,  ConnectionUser $actor): void;
}
