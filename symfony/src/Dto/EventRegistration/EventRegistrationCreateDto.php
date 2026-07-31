<?php

namespace App\Dto\EventRegistration;

use Symfony\Component\Validator\Constraints as Assert;

final class EventRegistrationCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public ?string $personId = null;

    #[Assert\Ulid]
    public ?string $membershipId = null;

    #[Assert\Length(max: 5000)]
    public ?string $note = null;
}
