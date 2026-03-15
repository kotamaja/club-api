<?php

namespace App\Mapper\CustomMapper\PersonContact;

use App\Dto\PersonContact\PersonContactPatchDto;
use App\Entity\PersonContact;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PersonContactPatchDto::class, target: PersonContact::class)]
final class PersonContactPatchDtoToPersonContactMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {

        if (!$source instanceof PersonContactPatchDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $personRelationship = $target instanceof PersonContact ? $target : new PersonContact();


        if ($source->isTypeProvided()) {
            $personRelationship->setType($source->getType());
        }
        if ($source->isEmergencyContactProvided()) {
            $personRelationship->setIsEmergencyContact((bool) $source->getIsEmergencyContact());
        }

        return $personRelationship;
    }
}
