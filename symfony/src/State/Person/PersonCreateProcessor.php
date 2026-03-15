<?php

namespace App\State\Person;

use App\Dto\Person\PersonCreateDto;
use App\Entity\Person;
use App\State\Util\AbstractCreateProcessor;

final class PersonCreateProcessor  extends AbstractCreateProcessor
{
    protected function entityClass(): string
    {
        return Person::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonCreateDto) {
            throw new \LogicException('Expected PersonCreateDto.');
        }
    }

}
