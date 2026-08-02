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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Handles unauthenticated public registration request submissions.
 *
 * This processor resolves the event from the public URL and deliberately avoids
 * using the current organization context, because public requests do not provide
 * an authenticated organization header.
 */
final readonly class PublicEventRegistrationRequestCreateProcessor implements ProcessorInterface
{
    public function __construct(private EventRepository                                                                      $eventRepository,
                                private PublicEventRegistrationRequestWriteServiceInterface                                  $writeService,
                                private MapperRegistry                                                                       $mapperRegistry,
                                private EntityManagerInterface                                                               $em,
                                private RequestStack                                                                         $requestStack,
                                #[Autowire(service: 'limiter.public_event_registration_request')] private RateLimiterFactory $rateLimiterFactory)
    {
    }

    /**
     * Creates a pending public registration request for an event.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PublicEventRegistrationRequestItemDto
    {

        $this->consumeRateLimit();

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

    /**
     * Applies a rate limit to public registration request submissions.
     *
     * The key is scoped by client IP and request path. This prevents submissions
     * for one public event from consuming the bucket of another event, while still
     * limiting repeated submissions to the same public form.
     */
    private function consumeRateLimit(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $ip = $request?->getClientIp() ?? 'unknown';
        $path = $request?->getPathInfo() ?? 'unknown';

        $key = hash('sha256', $ip . '|' . $path);

        $limit = $this->rateLimiterFactory
            ->create($key)
            ->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(
                retryAfter: max(1, $limit->getRetryAfter()->getTimestamp() - time()),
                message: 'Too many registration requests. Please try again later.',
            );
        }
    }




}
