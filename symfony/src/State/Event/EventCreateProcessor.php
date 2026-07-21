<?php

namespace App\State\Event;

use App\Dto\Event\EventCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\Event\EventWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventCreateProcessor extends AbstractCreateProcessor
{
    public function __construct(MapperRegistry                              $mapperRegistry,
                                EntityManagerInterface                      $em,
                                Security                                    $security,
                                private readonly EventWriteServiceInterface $eventWriteService
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof EventCreateDto) {
            throw new \LogicException('Expected EventCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof EventCreateDto);

        return $this->eventWriteService->create($data, $this->getCurrentConnectionUser());
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'An event with the same identifier already exists.';
    }
}
