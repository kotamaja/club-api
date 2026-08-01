<?php

namespace App\Dto\EventRegistrationRequest;

final class PublicEventRegistrationRequestListDto
{
    public string $id;

    public string $eventId;

    public string $eventTitle;

    public string $clubId;

    public string $clubName;

    public string $firstname;

    public string $lastname;

    public string $email;

    public string $status;

    public \DateTimeImmutable $requestedAt;

    public ?\DateTimeImmutable $reviewedAt = null;

    public ?string $reviewedById = null;

    public ?string $reviewedByDisplayName = null;
}
