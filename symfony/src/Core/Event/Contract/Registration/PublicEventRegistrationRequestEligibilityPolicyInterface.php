<?php

namespace App\Core\Event\Contract\Registration;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;

interface PublicEventRegistrationRequestEligibilityPolicyInterface
{
    /**
     * Ensures that a new public registration request can be submitted for the event and email.
     */
    public function assertCanSubmit(Event $event, string $email, \DateTimeImmutable $now): void;

    /**
     * Ensures that an existing public registration request can be accepted.
     */
    public function assertCanAccept(PublicEventRegistrationRequest $request, \DateTimeImmutable $now): void;
}
