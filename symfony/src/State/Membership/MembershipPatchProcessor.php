<?php

namespace App\State\Membership;

use App\Dto\Membership\MembershipPatchDto;
use App\Entity\Membership;
use App\Mapper\MapperRegistry;
use App\State\AbstractPatchProcessor;
use App\Write\Membership\MembershipWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class MembershipPatchProcessor extends AbstractPatchProcessor
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
        if (!$data instanceof MembershipPatchDto) {
            throw new \LogicException('Expected MembershipPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return Membership::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof MembershipPatchDto);
        \assert($entity instanceof Membership);

        $this->membershipWriteService->patch(
            $data,
            $entity,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(
        mixed  $data,
        object $entity,
        array  $context,
    ): string
    {
        return 'A membership with the same name already exists.';
    }
}
