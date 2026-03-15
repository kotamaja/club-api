<?php

namespace App\Dto\PersonRelationship;

use App\Enum\RelationshipType;

final class PersonRelationshipPatchDto
{
    private bool $typeProvided = false;
    private ?RelationshipType $type = null;

    private bool $isEmergencyContactProvided = false;
    private ?bool $isEmergencyContact = null;

    public function setType(?RelationshipType $type): void
    {
        $this->typeProvided = true;
        $this->type = $type;
    }

    public function isTypeProvided(): bool
    {
        return $this->typeProvided;
    }

    public function getType(): ?RelationshipType
    {
        return $this->type;
    }

    public function setIsEmergencyContact(?bool $value): void
    {
        $this->isEmergencyContactProvided = true;
        $this->isEmergencyContact = $value;
    }

    public function isEmergencyContactProvided(): bool
    {
        return $this->isEmergencyContactProvided;
    }

    public function getIsEmergencyContact(): ?bool
    {
        return $this->isEmergencyContact;
    }
}
