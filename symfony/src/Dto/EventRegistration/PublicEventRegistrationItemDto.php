<?php

namespace App\Dto\EventRegistration;

final class PublicEventRegistrationItemDto
{
    public string $id;

    public string $eventId;

    public string $eventTitle;

    public string $status;

    public \DateTimeImmutable $requestedAt;

    public ?string $note = null;
}
