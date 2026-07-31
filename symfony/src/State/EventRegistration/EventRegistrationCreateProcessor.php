<?php

namespace App\State\EventRegistration;

use App\Core\Event\Entity\Event;
use App\Core\Event\Repository\EventRepository;
use App\Dto\EventRegistration\EventRegistrationCreateDto;
use App\Mapper\MapperRegistry;
use App\State\AbstractCreateProcessor;
use App\Write\EventRegistration\EventRegistrationWriteServiceInterface;
use App\Write\Exception\ReferencedResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class EventRegistrationCreateProcessor extends AbstractCreateProcessor
{
    public function __construct(MapperRegistry $mapperRegistry,
                                EntityManagerInterface $em,
                                Security $security,
                                private readonly EventRepository $eventRepository,
                                private readonly EventRegistrationWriteServiceInterface $eventRegistrationWriteService
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof EventRegistrationCreateDto) {
            throw new \LogicException('Expected EventRegistrationCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof EventRegistrationCreateDto);

        $event = $this->getEvent($context);

        return $this->eventRegistrationWriteService->create(
            $data,
            $event,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(mixed $data, array $context): string
    {
        return 'An event registration with the same unique values already exists.';
    }

    private function getEvent(array $context): Event
    {
        $eventId = $context['uriVariables']['eventId'] ?? null;

        if (!\is_string($eventId) || $eventId === '') {
            throw new ReferencedResourceNotFoundException('Event not found.', 'eventId');
        }

        $event = $this->eventRepository->findOneBy([
            'publicId' => $eventId,
        ]);

        if (!$event instanceof Event) {
            throw new ReferencedResourceNotFoundException('Event not found.', 'eventId');
        }

        return $event;
    }
}
