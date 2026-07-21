<?php

namespace App\Write\Event;

use App\Core\Event\Entity\Event;

class EventPatchResult
{
    public function __construct(private Event $event, private bool $changed)
    {
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }
}
