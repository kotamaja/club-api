<?php

namespace App\Controller\Auth;

use App\Security\RefreshToken\InvalidRefreshTokenException;
use App\Security\RefreshToken\RefreshTokenCookieFactory;
use App\Security\RefreshToken\RefreshTokenExtractor;
use App\Security\RefreshToken\RefreshTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RefreshTokenController
{
    public function __construct(private RefreshTokenManager                                                  $refreshTokenManager,
                                private RefreshTokenExtractor                                                $refreshTokenExtractor,
                                private RefreshTokenCookieFactory                                            $refreshTokenCookieFactory,
                                private JWTTokenManagerInterface                                             $jwtTokenManager,
                                #[Autowire(service: 'limiter.auth_refresh')] private RateLimiterFactory      $authRefreshLimiter,
                                #[Autowire(service: 'limiter.auth_refresh_user')] private RateLimiterFactory $authRefreshUserLimiter,
    )
    {
    }

    #[Route('/api/v1/auth/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $ipLimit = $this->authRefreshLimiter
            ->create('ip:' . ($request->getClientIp() ?? 'unknown'))
            ->consume(1);


        if (!$ipLimit->isAccepted()) {
            return $this->tooManyRefreshAttemptsResponse($ipLimit);
        }

        $plainRefreshToken = $this->refreshTokenExtractor->extract($request);

        if ($plainRefreshToken === null) {
            return new JsonResponse(['message' => 'Missing or ambiguous refresh token.'], Response::HTTP_BAD_REQUEST);
        }

        $oldRefreshToken = $this->refreshTokenManager->findUsableToken($plainRefreshToken);

        if ($oldRefreshToken === null) {
            return new JsonResponse(['message' => 'Invalid refresh token.',], Response::HTTP_UNAUTHORIZED);
        }

        $connectionUser = $oldRefreshToken->getConnectionUser();

        $userLimit = $this->authRefreshUserLimiter
            ->create('connection_user:' . $connectionUser->getPublicId())
            ->consume(1);

        if (!$userLimit->isAccepted()) {
            return $this->tooManyRefreshAttemptsResponse($ipLimit);
        }



        try {
            $createdRefreshToken = $this->refreshTokenManager->rotatePlainTokenAtomically(
                plainToken: $plainRefreshToken,
                userAgent: $request->headers->get('User-Agent'),
                ipAddress: $request->getClientIp(),
            );
        } catch (InvalidRefreshTokenException) {
            return new JsonResponse([
                'message' => 'Invalid refresh token.',
            ], Response::HTTP_UNAUTHORIZED);
        }


        $jwt = $this->jwtTokenManager->create($connectionUser);

        $refreshTokenMode = $createdRefreshToken->entity->getMode();

        $responsePayload = [
            'token' => $jwt,
        ];

        if ($refreshTokenMode->returnsRefreshTokenInBody()) {
            $responsePayload['refreshToken'] = $createdRefreshToken->plainToken;
        }

        $response = new JsonResponse($responsePayload);

        if ($refreshTokenMode->usesCookie()) {
            $response->headers->setCookie(
                $this->refreshTokenCookieFactory->create(
                    plainRefreshToken: $createdRefreshToken->plainToken,
                    expiresAt: $createdRefreshToken->entity->getExpiresAt(),
                )
            );
        }

        return $response;

    }


    private function tooManyRefreshAttemptsResponse(RateLimit $limit): JsonResponse
    {
        $retryAfter = max(1, $limit->getRetryAfter()->getTimestamp() - time());

        return new JsonResponse(['message' => 'Too many refresh attempts.',],
            Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => (string)$retryAfter,
            ]);
    }

}
