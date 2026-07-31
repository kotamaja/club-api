<?php

namespace App\Write\EventRegistration;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Dto\EventRegistration\EventRegistrationCreateDto;
use App\Entity\ConnectionUser;

interface EventRegistrationWriteServiceInterface
{
    public function create(EventRegistrationCreateDto $input, Event $event, ConnectionUser $actor): EventRegistration;

    public function cancel(EventRegistration $registration, ConnectionUser $actor): EventRegistration;
}
