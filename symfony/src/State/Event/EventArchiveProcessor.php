<?php

namespace App\State\Event;

use App\Core\Event\Entity\Event;
use App\Entity\ConnectionUser;
use App\Mapper\MapperRegistry;
use App\Write\Event\EventWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventArchiveProcessor extends AbstractEventActionProcessor
{
    public function __construct(MapperRegistry $mapperRegistry,
                                EntityManagerInterface $em,
                                Security $security,
                                private readonly EventWriteServiceInterface $eventWriteService)
    {
        parent::__construct($mapperRegistry,$em, $security);
    }

    protected function processEvent(Event $event, ConnectionUser $actor, array $context): Event
    {
        return $this->eventWriteService->archive($event, $actor);
    }
}
