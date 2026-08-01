<?php

namespace App\State\PublicEventRegistrationRequest;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Repository\EventRepository;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestCreateDto;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestItemDto;
use App\Mapper\MapperRegistry;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use App\Write\Exception\ResourceConflictException;
use App\Write\PublicEventRegistrationRequest\PublicEventRegistrationRequestWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Handles unauthenticated public registration request submissions.
 *
 * This processor resolves the event from the public URL and deliberately avoids
 * using the current organization context, because public requests do not provide
 * an authenticated organization header.
 */
final readonly class PublicEventRegistrationRequestCreateProcessor implements ProcessorInterface
{
    public function __construct(private EventRepository                                            $eventRepository,
                                private PublicEventRegistrationRequestWriteServiceInterface       $writeService,
                                private MapperRegistry                                             $mapperRegistry,
                                private EntityManagerInterface                                     $em)
    {
    }

    /**
     * Creates a pending public registration request for an event.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PublicEventRegistrationRequestItemDto
    {
        if (!$data instanceof PublicEventRegistrationRequestCreateDto) {
            throw new \LogicException('Expected PublicEventRegistrationRequestCreateDto.');
        }

        $eventId = $uriVariables['eventId'] ?? null;

        if (!\is_string($eventId)) {
            throw new NotFoundHttpException('Event not found.');
        }

        $event = $this->eventRepository->findOneByPublicId($eventId);

        if (!$event instanceof Event) {
            throw new NotFoundHttpException('Event not found.');
        }

        try {
            $request = $this->writeService->create($data, $event);

            $this->em->flush();
        } catch (ReferencedResourceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ResourceConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        } catch (BusinessRuleViolationException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $this->mapperRegistry->map($request, PublicEventRegistrationRequestItemDto::class);
    }
}
