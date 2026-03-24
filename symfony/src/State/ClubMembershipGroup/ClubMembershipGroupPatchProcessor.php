<?php

namespace App\State\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Entity\Club;
use App\Entity\ClubMembershipGroup;
use App\State\Util\AbstractPatchProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClubMembershipGroupPatchProcessor extends AbstractPatchProcessor
{

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubMembershipGroupPatchDto) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s, got %s.',
                ClubMembershipGroupPatchDto::class,
                get_debug_type($data)
            ));
        }
    }

    /**
     * @param ClubMembershipGroupPatchDto $data
     * @param ClubMembershipGroup $entity
     * @param array $context
     * @return void
     */
    protected function afterMap(mixed $data, object $entity, array $context): void
    {
        if ($data->isClubIdProvided()) {
            $club = $this->em->getRepository(Club::class)->findOneBy(['publicId' => $data->getClubId()]);
            if (!$club instanceof Club) {
                throw new NotFoundHttpException('club not found.' . $data->getClubId());
            }
            $entity->setClub($club);
        }
    }

}
