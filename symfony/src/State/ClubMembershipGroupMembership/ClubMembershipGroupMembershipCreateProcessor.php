<?php

namespace App\State\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroupMembership\ClubMembershipGroupMembershipCreateDto;
use App\Entity\ClubMembershipGroup;
use App\Entity\ClubMembershipGroupMembership;
use App\Entity\Membership;
use App\Mapper\InvalidInputException;
use App\State\Util\AbstractCreateProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClubMembershipGroupMembershipCreateProcessor extends AbstractCreateProcessor
{

    protected function entityClass(): string
    {
        return ClubMembershipGroupMembership::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubMembershipGroupMembershipCreateDto) {
            throw new \LogicException('Expected ClubMembershipGroupMembershipCreateDto.');
        }
    }

    protected function beforePersist(mixed $data, object $entity, array $context): void
    {

        $group = $this->em->getRepository(ClubMembershipGroup::class)->findOneBy(['publicId' => $data->clubMembershipGroupId]);
        if (!$group instanceof ClubMembershipGroup) {
            throw new NotFoundHttpException('group not found.');
        }

        $membership = $this->em->getRepository(Membership::class)->findOneBy(['publicId' => $data->membershipId]);
        if (!$membership instanceof Membership) {
            throw new NotFoundHttpException('membership not found.');
        }

        if ($this->em->getRepository(ClubMembershipGroupMembership::class)->count(['group' => $group, 'membership' => $membership]) !== 0) {
            throw new InvalidInputException('This membership is already assigned to this group.');
        }

        $entity->setGroup($group);
        $entity->setMembership($membership);
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): ?string
    {
        return 'This membership is already assigned to this group.';
    }

}

