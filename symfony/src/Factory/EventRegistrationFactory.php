<?php

namespace App\Factory;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Entity\Membership;
use App\Entity\Person;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<EventRegistration>
 */
final class EventRegistrationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return EventRegistration::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'requestedAt' => new \DateTimeImmutable('2026-09-01T10:00:00+02:00'),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): EventRegistration {
            $event = $attributes['event'] ?? null;

            if (!$event instanceof Event) {
                throw new \LogicException('Missing required "event" attribute for EventRegistrationFactory.');
            }

            $person = $attributes['person'] ?? null;

            if (!$person instanceof Person) {
                throw new \LogicException('Missing required "person" attribute for EventRegistrationFactory.');
            }

            $membership = $attributes['membership'] ?? null;

            if ($membership !== null && !$membership instanceof Membership) {
                throw new \LogicException('"membership" attribute must be a Membership.');
            }

            $requestedAt = $attributes['requestedAt'] ?? new \DateTimeImmutable();

            if (!$requestedAt instanceof \DateTimeImmutable) {
                throw new \LogicException('"requestedAt" attribute must be a DateTimeImmutable.');
            }

            $waitlisted = $attributes['waitlisted'] ?? false;

            if ($waitlisted) {
                return EventRegistration::waitlist(
                    event: $event,
                    person: $person,
                    membership: $membership,
                    now: $requestedAt,
                );
            }

            return EventRegistration::register(
                event: $event,
                person: $person,
                membership: $membership,
                now: $requestedAt,
            );
        });
    }

    public function forEvent(Event $event): self
    {
        return $this->with([
            'event' => $event,
        ]);
    }

    public function forPerson(Person $person): self
    {
        return $this->with([
            'person' => $person,
        ]);
    }

    public function forMembership(Membership $membership): self
    {
        return $this->with([
            'person' => $membership->getPerson(),
            'membership' => $membership,
        ]);
    }

    public function registered(): self
    {
        return $this->with([
            'waitlisted' => false,
        ]);
    }

    public function waitlisted(): self
    {
        return $this->with([
            'waitlisted' => true,
        ]);
    }
}
