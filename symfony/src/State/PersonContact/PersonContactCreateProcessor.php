<?php

namespace App\State\PersonContact;

use App\Dto\PersonContact\PersonContactCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\PersonContact\PersonContactWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class PersonContactCreateProcessor extends AbstractCreateProcessor
{
    public function __construct(MapperRegistry                                      $mapperRegistry,
                                EntityManagerInterface                              $em,
                                Security                                            $security,
                                private readonly PersonContactWriteServiceInterface $personContactWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonContactCreateDto) {
            throw new \LogicException('Expected PersonContactCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof PersonContactCreateDto);

        return $this->personContactWriteService->create(
            $data,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'This relationship already exists for this person, contact person and type.';
    }
}
