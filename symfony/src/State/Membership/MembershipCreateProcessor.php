<?php

namespace App\State\Membership;

use App\Dto\Membership\MembershipCreateDto;
use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use App\State\Util\AbstractCreateProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class MembershipCreateProcessor extends AbstractCreateProcessor
{
    protected function entityClass(): string
    {
        return Membership::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof MembershipCreateDto) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s, got %s.',
                MembershipCreateDto::class,
                get_debug_type($data)
            ));
        }
    }

    protected function beforePersist(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof MembershipCreateDto);
        \assert($entity instanceof Membership);

        $person = $this->em->getRepository(Person::class)->findOneBy([
            'publicId' => $data->personId,
        ]);

        if (!$person instanceof Person) {
            throw new NotFoundHttpException('Person not found.');
        }

        $club = $this->em->getRepository(Club::class)->findOneBy([
            'publicId' => $data->clubId,
        ]);

        if (!$club instanceof Club) {
            throw new NotFoundHttpException('Club not found.');
        }

        $entity->setPerson($person);
        $entity->setClub($club);

        $isCreatingActiveMembership = null === $entity->getEndedAt();

        if (!$isCreatingActiveMembership) {
            return;
        }

        $existingActiveMembership = $this->em->getRepository(Membership::class)->findOneBy([
            'person' => $person,
            'club' => $club,
            'endedAt' => null,
        ]);

        if ($existingActiveMembership instanceof Membership) {
            throw new UnprocessableEntityHttpException(
                'This person already has an active membership for this club.'
            );
        }
    }

}
