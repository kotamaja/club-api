<?php

namespace App\Dto\EventRegistrationRequest;

final class PublicEventRegistrationRequestItemDto
{
    public string $id;

    public string $eventId;

    public string $eventTitle;

    public string $clubId;

    public string $clubName;

    public string $firstname;

    public string $lastname;

    public string $email;

    public ?string $note = null;

    public string $status;

    public \DateTimeImmutable $requestedAt;

    public ?\DateTimeImmutable $reviewedAt = null;

    public ?string $reviewedById = null;

    public ?string $reviewedByDisplayName = null;

    public ?string $createdPersonId = null;

    public ?string $eventRegistrationId = null;

    public ?string $rejectionReason = null;
}
