<?php

namespace App\Factory;

use App\Core\Event\Entity\Event;
use App\Core\Event\Enum\EventType;
use App\Entity\Club;
use App\Entity\Organization;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Event>
 */
final class EventFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Event::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(3),
            'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+00:00'),
            'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+00:00'),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): Event {
            $organization = $attributes['organization'] ?? null;

            if (!$organization instanceof Organization) {
                throw new \LogicException('Missing required "organization" attribute for EventFactory.');
            }

            $title = $attributes['title'] ?? null;

            if (!\is_string($title) || $title === '') {
                throw new \LogicException('Missing required "title" attribute for EventFactory.');
            }

            $startsAt = $attributes['startsAt'] ?? null;

            if (!$startsAt instanceof \DateTimeImmutable) {
                throw new \LogicException('Missing required "startsAt" attribute for EventFactory.');
            }

            $endsAt = $attributes['endsAt'] ?? null;

            if (!$endsAt instanceof \DateTimeImmutable) {
                throw new \LogicException('Missing required "endsAt" attribute for EventFactory.');
            }

            $event = Event::create(
                organization: $organization,
                title: $title,
                startsAt: $startsAt,
                endsAt: $endsAt,
            );

            $club = $attributes['club'] ?? null;

            if ($club !== null) {
                if (!$club instanceof Club) {
                    throw new \LogicException('"club" attribute must be a Club.');
                }

                $event->attachToClub($club);
            }

            $type = $attributes['type'] ?? null;

            if ($type !== null) {
                if (!$type instanceof EventType) {
                    throw new \LogicException('"type" attribute must be an EventType.');
                }

                $event->changeType($type);
            }

            if (\array_key_exists('description', $attributes)) {
                $event->changeDescription($attributes['description']);
            }

            if (\array_key_exists('location', $attributes)) {
                $event->changeLocation($attributes['location']);
            }

            if (\array_key_exists('timezone', $attributes)) {
                $timezone = $attributes['timezone'];

                if (!\is_string($timezone)) {
                    throw new \LogicException('"timezone" attribute must be a string.');
                }

                $event->changeTimezone($timezone);
            }

            if (\array_key_exists('allDay', $attributes)) {
                if ($attributes['allDay']) {
                    $event->markAsAllDay();
                } else {
                    $event->markAsTimed();
                }
            }

            if (\array_key_exists('capacity', $attributes)) {
                $event->changeCapacity($attributes['capacity']);
            }

            if (\array_key_exists('waitlistEnabled', $attributes)) {
                if ($attributes['waitlistEnabled']) {
                    $event->enableWaitlist();
                } else {
                    $event->disableWaitlist();
                }
            }

            if (\array_key_exists('publicRegistrationEnabled', $attributes)) {
                if ($attributes['publicRegistrationEnabled']) {
                    $event->enablePublicRegistration();
                } else {
                    $event->disablePublicRegistration();
                }
            }

            if (
                \array_key_exists('registrationStartsAt', $attributes)
                || \array_key_exists('registrationEndsAt', $attributes)
            ) {
                $event->changeRegistrationWindow(
                    $attributes['registrationStartsAt'] ?? null,
                    $attributes['registrationEndsAt'] ?? null,
                );
            }

            return $event;
        });
    }

    public function forOrganization(Organization $organization): self
    {
        return $this->with([
            'organization' => $organization,
        ]);
    }

    public function forClub(Club $club): self
    {
        return $this->with([
            'organization' => $club->getOrganization(),
            'club' => $club,
        ]);
    }
}
