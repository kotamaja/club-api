<?php

namespace App\Dto\Event;

use App\Core\Event\Enum\EventType;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class EventCreateDto
{
    #[Assert\Ulid]
    public ?string $clubId = null;

    public ?EventType $type = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    public ?string $title = null;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;

    #[Assert\Length(max: 255)]
    public ?string $location = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $startsAt = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $endsAt = null;

    #[Assert\Length(max: 64)]
    public ?string $timezone = null;

    public bool $allDay = false;

    #[Assert\Positive]
    public ?int $capacity = null;

    public bool $waitlistEnabled = false;

    public bool $publicRegistrationEnabled = false;

    public ?DateTimeImmutable $registrationStartsAt = null;

    public ?DateTimeImmutable $registrationEndsAt = null;
}
