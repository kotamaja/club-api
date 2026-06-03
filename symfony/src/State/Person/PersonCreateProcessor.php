<?php

namespace App\State\Person;

use App\Dto\Person\PersonCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\Person\PersonWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class PersonCreateProcessor extends AbstractCreateProcessor
{
    public function __construct(MapperRegistry                             $mapperRegistry,
                                EntityManagerInterface                     $em,
                                Security                                   $security,
                                private readonly PersonWriteServiceInterface $personWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonCreateDto) {
            throw new \LogicException('Expected PersonCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof PersonCreateDto);

        return $this->personWriteService->create(
            $data,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'A person with the same name already exists.';
    }
}
