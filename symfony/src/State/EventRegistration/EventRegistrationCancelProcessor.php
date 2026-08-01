<?php

namespace App\State\EventRegistration;

use App\Core\Event\Entity\EventRegistration;
use App\Entity\ConnectionUser;
use App\Mapper\MapperRegistry;
use App\Write\EventRegistration\EventRegistrationWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventRegistrationCancelProcessor extends AbstractEventRegistrationActionProcessor
{
    public function __construct(MapperRegistry $mapperRegistry,
                                EntityManagerInterface $em,
                                Security $security,
                                private readonly EventRegistrationWriteServiceInterface $eventRegistrationWriteService
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function processRegistration(EventRegistration $registration, ConnectionUser $actor, array $context): EventRegistration
    {
        return $this->eventRegistrationWriteService->cancel($registration, $actor);
    }
}
