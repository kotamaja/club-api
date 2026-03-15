<?php

namespace App\State\Club;

use App\Dto\Club\ClubPatchDto;
use App\State\Util\AbstractPatchProcessor;

final class ClubPatchProcessor extends AbstractPatchProcessor
{
    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubPatchDto) {
            throw new \LogicException('Expected ClubPatchDto.');
        }
    }


}
