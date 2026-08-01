<?php

namespace App\Dto\EventRegistrationRequest;

use Symfony\Component\Validator\Constraints as Assert;

final class PublicEventRegistrationRequestRejectDto
{
    #[Assert\Length(max: 5000)]
    public ?string $reason = null;
}
