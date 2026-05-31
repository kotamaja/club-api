<?php

namespace App\State\Club;

use App\Dto\Club\ClubPatchDto;
use App\Entity\Club;
use App\Mapper\MapperRegistry;
use App\State\AbstractPatchProcessor;
use App\Write\Club\ClubWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ClubPatchProcessor extends AbstractPatchProcessor
{
    public function __construct(MapperRegistry                             $mapperRegistry,
                                EntityManagerInterface                     $em,
                                Security                                   $security,
                                private readonly ClubWriteServiceInterface $clubWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof ClubPatchDto) {
            throw new \LogicException('Expected ClubPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return Club::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof ClubPatchDto);
        \assert($entity instanceof Club);

        $this->clubWriteService->patch(
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
        return 'A club with the same name already exists.';
    }
}
