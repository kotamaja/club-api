<?php

namespace App\Write\Event;

use App\Core\Event\Entity\Event;
use App\Entity\ConnectionUser;
use App\Entity\Organization;

class EventPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Organization $organization): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanUpdate(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanDelete(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanPublish(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanCancel(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanArchive(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }
}
