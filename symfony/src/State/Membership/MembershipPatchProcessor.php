<?php

namespace App\State\Membership;

use App\Dto\Membership\MembershipPatchDto;
use App\Entity\Membership;
use App\State\Util\AbstractPatchProcessor;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MembershipPatchProcessor  extends AbstractPatchProcessor
{

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof MembershipPatchDto) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s, got %s.',
                MembershipPatchDto::class,
                get_debug_type($data)
            ));
        }
    }

    protected function afterMap(mixed $data, object $entity, array $context): void
    {

        if (
            null !== $entity->getEndedAt()
            && null !== $entity->getJoinedAt()
            && $entity->getEndedAt() < $entity->getJoinedAt()
        ) {
            throw new UnprocessableEntityHttpException(
                'The end date must be greater than or equal to the join date.'
            );
        }

        $person = $entity->getPerson();
        $club = $entity->getClub();

        if (!$person || !$club) {
            throw new \LogicException('Membership must have a person and a club.');
        }

        $isActive = null === $entity->getEndedAt();

        // Si on rend le membership actif
        if ($isActive) {
            $existingActiveMembership = $this->em->getRepository(Membership::class)->findOneBy([
                'person' => $person,
                'club' => $club,
                'endedAt' => null,
            ]);

            // Attention : exclure soi-même
            if (
                $existingActiveMembership instanceof Membership &&
                $existingActiveMembership->getId() !== $entity->getId()
            ) {
                throw new UnprocessableEntityHttpException(
                    'This person already has an active membership for this club.'
                );
            }
        }
    }

}
