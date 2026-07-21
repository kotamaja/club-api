<?php

namespace App\Core\Event\Exception;

use RuntimeException;

final class ActiveEventRegistrationAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('An active registration already exists for this event and person.');
    }
}
