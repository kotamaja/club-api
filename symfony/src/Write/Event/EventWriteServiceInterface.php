<?php

namespace App\Write\Event;

use App\Core\Event\Entity\Event;
use App\Dto\Event\EventCreateDto;
use App\Dto\Event\EventPatchDto;
use App\Entity\ConnectionUser;

interface EventWriteServiceInterface
{
    public function create(EventCreateDto $input, ConnectionUser $actor): Event;

    public function patch(EventPatchDto $input, Event $event, ConnectionUser $actor): EventPatchResult;

    public function delete(Event $event, ConnectionUser $actor): void;

    public function publish(Event $event, ConnectionUser $actor): Event;

    public function cancel(Event $event, ConnectionUser $actor): Event;

    public function archive(Event $event, ConnectionUser $actor): Event;
}
