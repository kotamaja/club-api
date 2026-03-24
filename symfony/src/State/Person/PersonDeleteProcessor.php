<?php

namespace App\State\Person;

use App\Entity\Person;
use App\State\Util\AbstractDeleteProcessor;

final class PersonDeleteProcessor extends AbstractDeleteProcessor
{


    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof Person) {
            throw new \LogicException('Expected Person entity.');
        }

        if ($entity->getRelationshipsAsPerson()->count() > 0) {
            return 'Cannot delete person: relationshipsAsPerson still exist.' ;
        }

        if ($entity->getRelationshipsAsContactPerson()->count() > 0) {
            return 'Cannot delete person: relationshipsAsContactPerson still exist.';
        }

        if ($entity->getMemberships()->count() > 0) {
            return 'Cannot delete person: membership still exist.';
        }


        return null;
    }
}
