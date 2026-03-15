<?php

namespace App\Dto\PersonRelationship;

use App\Enum\RelationshipType;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    'this.subjectId !== this.relatedPersonId',
    message: 'A person cannot be related to themselves.'
)]
final class PersonRelationshipCreateDto
{

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $subjectId;

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $relatedPersonId;

    public RelationshipType $type;

    public bool $isEmergencyContact = false;
}
