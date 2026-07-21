<?php

namespace App\Community\Event\Policy\Capacity;

use App\Core\Event\Contract\Capacity\EventCapacityPolicyInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Exception\EventCapacityExceededException;

final readonly class CommunityEventCapacityPolicy implements EventCapacityPolicyInterface
{
    public function hasAvailableCapacity(Event $event): bool
    {
        return $event->hasAvailableCapacity();
    }

    public function assertHasAvailableCapacity(Event $event): void
    {
        if (!$this->hasAvailableCapacity($event)) {
            throw new EventCapacityExceededException();
        }
    }
}
