<?php

namespace App\Dto\PersonRelationship;

use App\Enum\RelationshipType;

final class PersonRelationshipViewDto
{
    public string $id; // publicId
    public string $subjectId;
    public string $relatedPersonId;

    public RelationshipType $type;
    public bool $isEmergencyContact;
}
