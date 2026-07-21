<?php

namespace App\Core\Event\Exception;

use RuntimeException;

final class EventRegistrationNotAllowedException extends RuntimeException
{
    public function __construct(string $reason)
    {
        parent::__construct($reason);
    }
}
