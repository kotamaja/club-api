<?php

namespace App\Write\PublicEventRegistrationRequest;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestCreateDto;

interface PublicEventRegistrationRequestWriteServiceInterface
{
    /**
     * Creates a pending public registration request for a club event.
     */
    public function create(PublicEventRegistrationRequestCreateDto $input, Event $event): PublicEventRegistrationRequest;
}
