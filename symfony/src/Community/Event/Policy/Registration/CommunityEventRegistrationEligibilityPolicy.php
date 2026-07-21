<?php

namespace App\Community\Event\Policy\Registration;

use App\Core\Event\Contract\Registration\EventRegistrationEligibilityPolicyInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Entity\Person;
use DateTimeImmutable;

final readonly class CommunityEventRegistrationEligibilityPolicy implements EventRegistrationEligibilityPolicyInterface
{
    public function canRegister(Event $event, Person $person, DateTimeImmutable $now): bool
    {
        try {
            $this->assertCanRegister($event, $person, $now);

            return true;
        } catch (EventRegistrationNotAllowedException) {
            return false;
        }
    }

    public function assertCanRegister(Event $event, Person $person, DateTimeImmutable $now): void
    {
        if (!$event->acceptsRegistrations()) {
            throw new EventRegistrationNotAllowedException('Event does not accept registrations.');
        }

        $registrationStartsAt = $event->getRegistrationStartsAt();

        if ($registrationStartsAt !== null && $now < $registrationStartsAt) {
            throw new EventRegistrationNotAllowedException('Event registration is not open yet.');
        }

        $registrationEndsAt = $event->getRegistrationEndsAt();

        if ($registrationEndsAt !== null && $now > $registrationEndsAt) {
            throw new EventRegistrationNotAllowedException('Event registration is closed.');
        }
    }
}
