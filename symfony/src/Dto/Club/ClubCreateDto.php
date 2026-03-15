<?php

namespace App\Dto\Club;

use Symfony\Component\Validator\Constraints as Assert;

final class ClubCreateDto
{


    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public ?string $name = null;

}
