<?php

namespace App\Core\Event\Contract\Capacity;

use App\Core\Event\Entity\Event;

interface EventCapacityPolicyInterface
{
    public function hasAvailableCapacity(Event $event): bool;

    public function assertHasAvailableCapacity(Event $event): void;
}
