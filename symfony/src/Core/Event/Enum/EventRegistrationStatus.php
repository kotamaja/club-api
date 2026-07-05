<?php

namespace App\Core\Event\Enum;

enum EventRegistrationStatus: string
{
    case Registered = 'registered';
    case Waitlisted = 'waitlisted';
    case Cancelled = 'cancelled';

    public function isActive(): bool
    {
        return $this === self::Registered || $this === self::Waitlisted;
    }

    public function consumesCapacity(): bool
    {
        return $this === self::Registered;
    }
}
