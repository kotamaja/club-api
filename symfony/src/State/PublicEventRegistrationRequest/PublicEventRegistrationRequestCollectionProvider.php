<?php

namespace App\State\PublicEventRegistrationRequest;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Repository\EventRepository;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\State\CollectionProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Guards the parent event before delegating collection loading.
 *
 * This provider intentionally does not load the collection itself. It only
 * ensures that the parent event exists in the current organization, then
 * delegates to the generic collection provider to keep pagination, filters
 * and sorting behavior.
 */
final readonly class PublicEventRegistrationRequestCollectionProvider implements ProviderInterface
{
    public function __construct(private EventRepository             $eventRepository,
                                private CurrentOrganizationContext $currentOrganizationContext,
                                private CollectionProvider         $collectionProvider)
    {
    }

    /**
     * Provides public registration requests for a visible parent event.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []):object|array|null
    {
        if (!$operation instanceof CollectionOperationInterface) {
            throw new \LogicException('Expected a collection operation.');
        }

        $eventId = $uriVariables['eventId'] ?? null;

        if (!\is_string($eventId)) {
            throw new NotFoundHttpException('Event not found.');
        }

        $event = $this->eventRepository->findOneByPublicId($eventId);

        if (!$event instanceof Event) {
            throw new NotFoundHttpException('Event not found.');
        }

        if ($event->getOrganization() !== $this->currentOrganizationContext->getOrganization()) {
            throw new NotFoundHttpException('Event not found.');
        }

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
