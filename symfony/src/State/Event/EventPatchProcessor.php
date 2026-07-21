<?php

namespace App\State\Event;

use App\Core\Event\Entity\Event;
use App\Dto\Event\EventPatchDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractPatchProcessor;
use App\Write\Event\EventWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventPatchProcessor extends AbstractPatchProcessor
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
        if (!$data instanceof EventPatchDto) {
            throw new \LogicException('Expected EventPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return Event::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof EventPatchDto);
        \assert($entity instanceof Event);

        $this->eventWriteService->patch($data, $entity, $this->getCurrentConnectionUser());
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): string
    {
        return 'An event with the same identifier already exists.';
    }
}
