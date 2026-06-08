<?php

namespace App\Dto\ClubMembershipGroup;

use App\Dto\Club\ClubListDto;

class ClubMembershipGroupPatchDto
{


    private ?string $name;

    private ?string $description;



    private bool $nameProvided = false;
    private bool $descriptionProvided = false;



    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->nameProvided = true;
        $this->name = $name;
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


    public function isNameProvided(): bool
    {
        return $this->nameProvided;
    }

    public function isDescriptionProvided(): bool
    {
        return $this->descriptionProvided;
    }



}
