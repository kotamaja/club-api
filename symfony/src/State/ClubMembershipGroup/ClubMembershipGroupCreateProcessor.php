<?php

namespace App\State\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupCreateDto;
use App\Entity\Club;
use App\Entity\ClubMembershipGroup;
use App\Entity\Person;
use App\State\Util\AbstractCreateProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClubMembershipGroupCreateProcessor extends AbstractCreateProcessor
{


    protected function entityClass(): string
    {
        return ClubMembershipGroup::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubMembershipGroupCreateDto) {
            throw new \LogicException('Expected ClubMembershipGroupCreateDto.');
        }
    }


    protected function beforePersist(mixed $data, object $entity, array $context): void
    {

        $club = $this->em->getRepository(Club::class)->findOneBy([
            'publicId' => $data->clubId,
        ]);
        if (!$club instanceof Club) {
            throw new NotFoundHttpException('club not found.');
        }

        $entity->setClub($club);
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): ?string
    {
        return 'This relationship already exists.';
    }
}
