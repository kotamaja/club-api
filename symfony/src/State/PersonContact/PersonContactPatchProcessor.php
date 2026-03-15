<?php

namespace App\State\PersonContact;

use App\Dto\PersonContact\PersonContactPatchDto;
use App\State\Util\AbstractPatchProcessor;

class PersonContactPatchProcessor extends AbstractPatchProcessor
{
    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonContactPatchDto) {
            throw new \LogicException('Expected PersonContactPatchDto.');
        }
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): ?string
    {
        return 'This relationship already exists.';
    }
}
