<?php

namespace App\Dto\PersonContact;

use App\Enum\RelationshipType;

final class PersonContactViewDto
{
    public string $id; // publicId
    public string $personId;
    public string $contactPersonId;

    public RelationshipType $type;
    public bool $isEmergencyContact;
}
