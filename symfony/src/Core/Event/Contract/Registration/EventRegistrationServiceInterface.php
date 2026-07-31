<?php

namespace App\Core\Event\Contract\Registration;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Entity\Membership;
use App\Entity\Person;
use DateTimeImmutable;

interface EventRegistrationServiceInterface
{
    public function register(Event $event, Person $person, ?Membership $membership = null, ?DateTimeImmutable $now = null): EventRegistration;

    public function cancel(EventRegistration $registration, ?DateTimeImmutable $now = null): EventRegistration;
}
