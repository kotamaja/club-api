<?php

namespace App\State\PersonRelationship;

use App\Dto\PersonRelationship\PersonRelationshipPatchDto;
use App\State\Util\AbstractPatchProcessor;

class PersonRelationshipPatchProcessor extends AbstractPatchProcessor
{
    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonRelationshipPatchDto) {
            throw new \LogicException('Expected PersonRelationshipPatchDto.');
        }
    }
}
