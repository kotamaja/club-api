<?php

namespace App\Dto\Utils;

use DateTimeImmutable;
use InvalidArgumentException;

class DateUtils
{
    public static function fromString(?string $value, string $format): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat($format, $value);

        if ($date === false) {
            throw new InvalidArgumentException(sprintf('Invalid date format "%s", expected Y-m-d.', $value));
        }

        return $date;
    }


    public static function toString(?\DateTimeInterface $date, string $format): ?string
    {
        return $date?->format($format);
    }

}

