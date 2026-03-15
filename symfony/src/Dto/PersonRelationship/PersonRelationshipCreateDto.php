<?php

namespace App\Dto\PersonRelationship;

use App\Enum\RelationshipType;
use Symfony\Component\Validator\Constraints as Assert;

final class PersonRelationshipCreateDto
{

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 26)]
    public string $subjectId;

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 26)]
    public string $relatedPersonId;

    public RelationshipType $type;

    public bool $isEmergencyContact = false;
}
