<?php

namespace App\Core\Event\Enum;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function acceptsRegistrations(): bool
    {
        return $this === self::Published;
    }
}
