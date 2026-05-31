<?php

namespace App\EventListener;

use App\Entity\ConnectionUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: RequestEvent::class, method: 'onKernelRequest', priority: 5)]
final readonly class AuthenticatedApiRateLimitListener
{
    public function __construct(private Security                                                             $security,
                                #[Autowire(service: 'limiter.authenticated_api')] private RateLimiterFactory $authenticatedApiLimiter,
    )
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        if (str_starts_with($request->getPathInfo(), '/api/auth/')) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            return;
        }

        $limiterKey = 'connection_user:' . $user->getPublicId();

        $limit = $this->authenticatedApiLimiter
            ->create($limiterKey)
            ->consume(1);

        if ($limit->isAccepted()) {
            return;
        }

        $retryAfter = max(1, $limit->getRetryAfter()->getTimestamp() - time());

        $event->setResponse(new JsonResponse([
            'message' => 'Too many API requests.',
        ], Response::HTTP_TOO_MANY_REQUESTS, [
            'Retry-After' => (string)$retryAfter,
        ]));
    }
}
