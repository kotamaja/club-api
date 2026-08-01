<?php

namespace App\Core\Event\Exception;

final class PublicEventRegistrationRequestRejectedException extends \RuntimeException
{
    public function __construct(string $reason)
    {
        parent::__construct($reason);
    }
}
