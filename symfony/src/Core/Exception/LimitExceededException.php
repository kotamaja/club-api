<?php

namespace App\Core\Exception;

use App\Core\Enum\Limit;
use RuntimeException;

final class LimitExceededException extends RuntimeException
{
    public function __construct(private readonly Limit $limit, private readonly int $max, private readonly int $currentValue, private readonly int $increment = 1)
    {
        parent::__construct(sprintf(
            'Limit "%s" exceeded. Maximum allowed: %d, current value: %d, requested increment: %d.',
            $limit->value,
            $max,
            $currentValue,
            $increment,
        ));
    }

    public function getLimit(): Limit
    {
        return $this->limit;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function getCurrentValue(): int
    {
        return $this->currentValue;
    }

    public function getIncrement(): int
    {
        return $this->increment;
    }
}
