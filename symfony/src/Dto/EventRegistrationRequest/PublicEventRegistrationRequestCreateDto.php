<?php

namespace App\Dto\EventRegistrationRequest;

use Symfony\Component\Validator\Constraints as Assert;

final class PublicEventRegistrationRequestCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public ?string $firstname = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public ?string $lastname = null;

    #[Assert\NotBlank]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_HTML5)]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(max: 1000)]
    public ?string $note = null;

    /**
     * Honeypot field.
     *
     * This field must stay empty. It is intended to catch basic bots that
     * blindly fill every input field in the form.
     */
    #[Assert\Length(max: 255)]
    public ?string $homepage = null;
}
