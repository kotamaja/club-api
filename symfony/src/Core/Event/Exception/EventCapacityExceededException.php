<?php

namespace App\Core\Event\Exception;

use RuntimeException;

final class EventCapacityExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Event capacity has been reached.');
    }
}
