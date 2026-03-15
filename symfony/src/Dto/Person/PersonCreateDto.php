<?php

namespace App\Dto\Person;


use Symfony\Component\Validator\Constraints as Assert;

final class PersonCreateDto
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public ?string $firstname = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public ?string $lastname = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

}
