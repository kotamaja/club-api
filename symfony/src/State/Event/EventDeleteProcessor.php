<?php

namespace App\State\Event;

use App\Core\Event\Entity\Event;
use App\State\AbstractDeleteProcessor;
use App\Write\Event\EventWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventDeleteProcessor extends AbstractDeleteProcessor
{
    public function __construct(EntityManagerInterface                      $em,
                                Security                                    $security,
                                private readonly EventWriteServiceInterface $eventWriteService
    )
    {
        parent::__construct($em, $security);
    }

    protected function entityClass(): string
    {
        return Event::class;
    }

    protected function deleteEntity(object $entity, array $context): void
    {
        \assert($entity instanceof Event);

        $this->eventWriteService->delete($entity, $this->getCurrentConnectionUser());
    }
}
