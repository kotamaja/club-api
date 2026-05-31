<?php

namespace App\State\Club;

use App\Dto\Club\ClubCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\Club\ClubWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ClubCreateProcessor extends AbstractCreateProcessor
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
        if (!$data instanceof ClubCreateDto) {
            throw new \LogicException('Expected ClubCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof ClubCreateDto);

        return $this->clubWriteService->create(
            $data,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'A club with the same name already exists.';
    }
}
