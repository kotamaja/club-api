<?php

namespace App\Dto\Event;

use App\Core\Event\Enum\EventType;
use Symfony\Component\Validator\Constraints as Assert;

final class EventPatchDto
{
    #[Assert\Ulid]
    private ?string $clubId = null;
    private bool $clubIdProvided = false;

    private ?EventType $type = null;
    private bool $typeProvided = false;

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 180)]
    private ?string $title = null;
    private bool $titleProvided = false;

    #[Assert\Length(max: 5000)]
    private ?string $description = null;
    private bool $descriptionProvided = false;

    #[Assert\Length(max: 255)]
    private ?string $location = null;
    private bool $locationProvided = false;

    private ?\DateTimeImmutable $startsAt = null;
    private bool $startsAtProvided = false;

    private ?\DateTimeImmutable $endsAt = null;
    private bool $endsAtProvided = false;

    #[Assert\Length(max: 64)]
    private ?string $timezone = null;
    private bool $timezoneProvided = false;

    private ?bool $allDay = null;
    private bool $allDayProvided = false;

    #[Assert\Positive]
    private ?int $capacity = null;
    private bool $capacityProvided = false;

    private ?bool $waitlistEnabled = null;
    private bool $waitlistEnabledProvided = false;

    private ?bool $publicRegistrationEnabled = null;
    private bool $publicRegistrationEnabledProvided = false;

    private ?\DateTimeImmutable $registrationStartsAt = null;
    private bool $registrationStartsAtProvided = false;

    private ?\DateTimeImmutable $registrationEndsAt = null;
    private bool $registrationEndsAtProvided = false;

    public function getClubId(): ?string
    {
        return $this->clubId;
    }

    public function setClubId(?string $clubId): void
    {
        $this->clubIdProvided = true;
        $this->clubId = $clubId;
    }

    public function isClubIdProvided(): bool
    {
        return $this->clubIdProvided;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(?EventType $type): void
    {
        $this->typeProvided = true;
        $this->type = $type;
    }

    public function isTypeProvided(): bool
    {
        return $this->typeProvided;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->titleProvided = true;
        $this->title = $title;
    }

    public function isTitleProvided(): bool
    {
        return $this->titleProvided;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->descriptionProvided = true;
        $this->description = $description;
    }

    public function isDescriptionProvided(): bool
    {
        return $this->descriptionProvided;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->locationProvided = true;
        $this->location = $location;
    }

    public function isLocationProvided(): bool
    {
        return $this->locationProvided;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): void
    {
        $this->startsAtProvided = true;
        $this->startsAt = $startsAt;
    }

    public function isStartsAtProvided(): bool
    {
        return $this->startsAtProvided;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): void
    {
        $this->endsAtProvided = true;
        $this->endsAt = $endsAt;
    }

    public function isEndsAtProvided(): bool
    {
        return $this->endsAtProvided;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezoneProvided = true;
        $this->timezone = $timezone;
    }

    public function isTimezoneProvided(): bool
    {
        return $this->timezoneProvided;
    }

    public function getAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(?bool $allDay): void
    {
        $this->allDayProvided = true;
        $this->allDay = $allDay;
    }

    public function isAllDayProvided(): bool
    {
        return $this->allDayProvided;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): void
    {
        $this->capacityProvided = true;
        $this->capacity = $capacity;
    }

    public function isCapacityProvided(): bool
    {
        return $this->capacityProvided;
    }

    public function getWaitlistEnabled(): ?bool
    {
        return $this->waitlistEnabled;
    }

    public function setWaitlistEnabled(?bool $waitlistEnabled): void
    {
        $this->waitlistEnabledProvided = true;
        $this->waitlistEnabled = $waitlistEnabled;
    }

    public function isWaitlistEnabledProvided(): bool
    {
        return $this->waitlistEnabledProvided;
    }

    public function getPublicRegistrationEnabled(): ?bool
    {
        return $this->publicRegistrationEnabled;
    }

    public function setPublicRegistrationEnabled(?bool $publicRegistrationEnabled): void
    {
        $this->publicRegistrationEnabledProvided = true;
        $this->publicRegistrationEnabled = $publicRegistrationEnabled;
    }

    public function isPublicRegistrationEnabledProvided(): bool
    {
        return $this->publicRegistrationEnabledProvided;
    }

    public function getRegistrationStartsAt(): ?\DateTimeImmutable
    {
        return $this->registrationStartsAt;
    }

    public function setRegistrationStartsAt(?\DateTimeImmutable $registrationStartsAt): void
    {
        $this->registrationStartsAtProvided = true;
        $this->registrationStartsAt = $registrationStartsAt;
    }

    public function isRegistrationStartsAtProvided(): bool
    {
        return $this->registrationStartsAtProvided;
    }

    public function getRegistrationEndsAt(): ?\DateTimeImmutable
    {
        return $this->registrationEndsAt;
    }

    public function setRegistrationEndsAt(?\DateTimeImmutable $registrationEndsAt): void
    {
        $this->registrationEndsAtProvided = true;
        $this->registrationEndsAt = $registrationEndsAt;
    }

    public function isRegistrationEndsAtProvided(): bool
    {
        return $this->registrationEndsAtProvided;
    }
}
