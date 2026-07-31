<?php

namespace App\Dto\EventRegistration;

final class EventRegistrationListDto
{
    public string $id;

    public string $eventId;

    public string $eventTitle;

    public string $personId;

    public string $personFirstname;

    public string $personLastname;

    public ?string $membershipId = null;

    public string $status;

    public \DateTimeImmutable $requestedAt;

    public ?\DateTimeImmutable $cancelledAt = null;

    public ?string $note = null;
}
