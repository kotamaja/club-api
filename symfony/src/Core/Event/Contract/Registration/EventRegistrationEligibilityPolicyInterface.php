<?php

namespace App\Core\Event\Contract\Registration;

use App\Core\Event\Entity\Event;
use App\Entity\Person;

interface EventRegistrationEligibilityPolicyInterface
{
    public function canRegister(Event $event, Person $person, \DateTimeImmutable $now): bool;

    public function assertCanRegister(Event $event, Person $person, \DateTimeImmutable $now): void;
}
