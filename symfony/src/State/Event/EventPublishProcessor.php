<?php

namespace App\State\Event;

use App\Core\Event\Entity\Event;
use App\Entity\ConnectionUser;
use App\Mapper\MapperRegistry;
use App\Write\Event\EventWriteServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventPublishProcessor extends AbstractEventActionProcessor
{
    public function __construct(MapperRegistry $mapperRegistry, Security $security, private readonly EventWriteServiceInterface $eventWriteService)
    {
        parent::__construct($mapperRegistry, $security);
    }

    protected function processEvent(Event $event, ConnectionUser $actor, array $context): Event
    {
        return $this->eventWriteService->publish($event, $actor);
    }
}
