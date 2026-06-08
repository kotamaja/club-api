<?php

namespace App\State\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Entity\ClubMembershipGroup;
use App\Mapper\MapperRegistry;
use App\State\AbstractPatchProcessor;
use App\Write\ClubMembershipGroup\ClubMembershipGroupWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ClubMembershipGroupPatchProcessor extends AbstractPatchProcessor
{
    public function __construct(MapperRegistry                                            $mapperRegistry,
                                EntityManagerInterface                                    $em,
                                Security                                                  $security,
                                private readonly ClubMembershipGroupWriteServiceInterface $clubMembershipGroupWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubMembershipGroupPatchDto) {
            throw new \LogicException('Expected ClubMembershipGroupPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return ClubMembershipGroup::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof ClubMembershipGroupPatchDto);
        \assert($entity instanceof ClubMembershipGroup);

        $this->clubMembershipGroupWriteService->patch(
            $data,
            $entity,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): string
    {
        return 'A membership group with the same name already exists in this club.';
    }
}
