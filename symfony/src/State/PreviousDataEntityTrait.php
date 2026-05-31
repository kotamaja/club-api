<?php

namespace App\State;

trait PreviousDataEntityTrait
{
    protected function getEntityFromContext(array $context, string $expectedClass): object
    {
        $entity = $context['previous_data'] ?? null;

        if (!$entity instanceof $expectedClass) {
            throw new \LogicException(sprintf(
                'Expected previous_data to be an instance of %s.',
                $expectedClass,
            ));
        }

        if ($this->em->contains($entity)) {
            return $entity;
        }

        if (!method_exists($entity, 'getId')) {
            throw new \LogicException(sprintf(
                'Detached %s cannot be reattached because it has no getId() method.',
                $expectedClass,
            ));
        }

        $id = $entity->getId();

        if ($id === null) {
            throw new \LogicException(sprintf(
                'Detached %s cannot be reattached because its id is null.',
                $expectedClass,
            ));
        }

        return $this->em->getReference($expectedClass, $id);
    }
}
