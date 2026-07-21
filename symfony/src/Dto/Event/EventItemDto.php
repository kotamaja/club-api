<?php

namespace App\Dto\Event;

final class EventItemDto
{
    public string $id;

    public string $title;

    public ?string $description = null;

    public ?string $location = null;

    public string $type;

    public string $status;

    public ?string $clubId = null;

    public ?string $clubName = null;

    public \DateTimeImmutable $startsAt;

    public \DateTimeImmutable $endsAt;

    public string $timezone;

    public bool $allDay;

    public ?int $capacity = null;

    public int $registeredCount = 0;

    public int $waitlistedCount = 0;

    public bool $waitlistEnabled = false;

    public bool $publicRegistrationEnabled = false;

    public ?\DateTimeImmutable $registrationStartsAt = null;

    public ?\DateTimeImmutable $registrationEndsAt = null;

    public ?string $myRegistrationStatus = null;
}
