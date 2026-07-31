<?php

namespace App\Write\EventRegistration;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Entity\ConnectionUser;

class EventRegistrationPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanCancel(ConnectionUser $actor, EventRegistration $registration): void
    {
        // TODO: implement real permission check.
    }
}
