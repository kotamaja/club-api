<?php

namespace App\State\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\ClubMembershipGroup\ClubMembershipGroupWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ClubMembershipGroupCreateProcessor extends AbstractCreateProcessor
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
        if (!$data instanceof ClubMembershipGroupCreateDto) {
            throw new \LogicException('Expected ClubMembershipGroupCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof ClubMembershipGroupCreateDto);

        return $this->clubMembershipGroupWriteService->create(
            $data,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'A membership group with the same name already exists in this club.';
    }
}
