<?php

namespace App\State\Club;

use App\Dto\Club\ClubCreateDto;
use App\Entity\Club;
use App\State\Util\AbstractCreateProcessor;

final class ClubCreateProcessor extends AbstractCreateProcessor
{
    protected function entityClass(): string
    {
        return Club::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubCreateDto) {
            throw new \LogicException('Expected ClubCreateDto.');
        }
    }

}
