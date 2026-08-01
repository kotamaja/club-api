<?php

namespace App\Core\Event\Enum;

enum PublicEventRegistrationRequestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isAccepted(): bool
    {
        return $this === self::Accepted;
    }

    public function isRejected(): bool
    {
        return $this === self::Rejected;
    }
}
