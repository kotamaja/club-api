<?php

namespace App\State\Membership;

use App\Dto\Membership\MembershipCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\Membership\MembershipWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class MembershipCreateProcessor extends AbstractCreateProcessor
{
    public function __construct(MapperRegistry                                   $mapperRegistry,
                                EntityManagerInterface                           $em,
                                Security                                         $security,
                                private readonly MembershipWriteServiceInterface $membershipWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof MembershipCreateDto) {
            throw new \LogicException('Expected MembershipCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof MembershipCreateDto);

        return $this->membershipWriteService->create(
            $data,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'A Membership with the same name already exists.';
    }
}
