<?php

namespace App\Dto\Event;

final class EventListDto
{
    public string $id;

    public string $title;

    public string $type;

    public string $status;

    public ?string $clubId = null;

    public ?string $clubName = null;

    public ?string $location = null;

    public \DateTimeImmutable $startsAt;

    public \DateTimeImmutable $endsAt;

    public string $timezone;

    public bool $allDay;

    public ?int $capacity = null;

    public int $registeredCount = 0;

    public bool $waitlistEnabled = false;

    public bool $publicRegistrationEnabled = false;

    public ?string $myRegistrationStatus = null;
}
