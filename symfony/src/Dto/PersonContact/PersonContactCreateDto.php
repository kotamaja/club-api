<?php

namespace App\Dto\PersonContact;

use App\Enum\RelationshipType;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    'this.personId !== this.contactPersonId',
    message: 'A person cannot be related to themselves.'
)]
final class PersonContactCreateDto
{

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $personId;

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $contactPersonId;

    #[Assert\NotNull]
    public RelationshipType $type;

    public bool $isEmergencyContact = false;
}
