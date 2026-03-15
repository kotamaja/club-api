<?php

namespace App\State\Person;

use App\Dto\Person\PersonPatchDto;
use App\State\Util\AbstractPatchProcessor;

final class PersonPatchProcessor  extends AbstractPatchProcessor
{
    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonPatchDto) {
            throw new \LogicException('Expected PersonPatchDto.');
        }
    }


}
