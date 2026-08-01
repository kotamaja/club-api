<?php

namespace App\Dto\EventRegistration;

use Symfony\Component\Validator\Constraints as Assert;

final class PublicEventRegistrationCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public ?string $firstname = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public ?string $lastname = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(max: 5000)]
    public ?string $note = null;
}
