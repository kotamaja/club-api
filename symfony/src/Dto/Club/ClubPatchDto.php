<?php

namespace App\Dto\Club;

use Symfony\Component\Validator\Constraints as Assert;

final class ClubPatchDto
{

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 150)]
    private ?string $name = null;

    private bool $nameProvided = false;

    public function setName(?string $name): void
    {
        $this->nameProvided = true;
        $this->name = $name;
    }

    public function isNameProvided(): bool
    {
        return $this->nameProvided;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
