<?php

namespace App\State\EventRegistration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Core\Event\Repository\EventRepository;
use App\Dto\EventRegistration\EventRegistrationListDto;
use App\Mapper\MapperRegistry;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<EventRegistrationListDto>
 */
final class EventRegistrationCollectionProvider implements ProviderInterface
{
    public function __construct(private readonly EventRepository             $eventRepository,
                                private readonly EventRegistrationRepository $eventRegistrationRepository,
                                private readonly OrganizationScopeGuard      $organizationScopeGuard,
                                private readonly MapperRegistry              $mapperRegistry)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $eventId = $uriVariables['eventId'] ?? null;

        if (!\is_string($eventId) || $eventId === '') {
            throw new NotFoundHttpException();
        }

        $event = $this->eventRepository->findOneBy([
            'publicId' => $eventId,
        ]);

        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);

        $registrations = $this->eventRegistrationRepository->findByEventOrderedByRequestedAt($event);

        return \array_map(
            fn($registration): EventRegistrationListDto => $this->mapperRegistry->map($registration, EventRegistrationListDto::class),
            $registrations);
    }
}
