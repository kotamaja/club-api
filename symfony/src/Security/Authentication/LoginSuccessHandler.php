<?php

namespace App\Security\Authentication;

use App\Entity\ConnectionUser;
use App\Security\RefreshToken\RefreshTokenCookieFactory;
use App\Security\RefreshToken\RefreshTokenManager;
use App\Security\RefreshToken\RefreshTokenMode;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private JWTTokenManagerInterface  $jwtTokenManager,
                                private RefreshTokenManager       $refreshTokenManager,
                                private RefreshTokenCookieFactory $refreshTokenCookieFactory,
                                private EntityManagerInterface    $entityManager)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();

        if (!$user instanceof ConnectionUser) {
            return new JsonResponse(['message' => 'Invalid authenticated user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $payload = $this->decodeJsonPayload($request);
        $refreshTokenMode = RefreshTokenMode::fromRequestValue($payload['refreshTokenMode'] ?? null);


        $user->setLastLoginAt(new \DateTimeImmutable());

        $createdRefreshToken = null;

        if ($refreshTokenMode->createsRefreshToken()) {
            $createdRefreshToken = $this->refreshTokenManager->createForUser(
                connectionUser: $user,
                mode: $refreshTokenMode,
                userAgent: $request->headers->get('User-Agent'),
                ipAddress: $request->getClientIp(),
            );
        }

        $this->entityManager->flush();

        $jwt = $this->jwtTokenManager->create($user);

        $responsePayload = ['token' => $jwt
        ];

        if ($refreshTokenMode->returnsRefreshTokenInBody() && $createdRefreshToken !== null) {
            $responsePayload['refreshToken'] = $createdRefreshToken->plainToken;
        }

        $response = new JsonResponse($responsePayload);

        if ($refreshTokenMode->usesCookie() && $createdRefreshToken !== null) {
            $response->headers->setCookie(
                $this->refreshTokenCookieFactory->create(
                    plainRefreshToken: $createdRefreshToken->plainToken,
                    expiresAt: $createdRefreshToken->entity->getExpiresAt(),
                )
            );
        }

        return $response;

    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonPayload(Request $request): array
    {
        $content = $request->getContent();

        if ($content === '') {
            return [];
        }

        $payload = json_decode($content, true);

        return is_array($payload) ? $payload : [];
    }
}
