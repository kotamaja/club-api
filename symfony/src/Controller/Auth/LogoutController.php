<?php

namespace App\Controller\Auth;

use App\Security\RefreshToken\RefreshTokenCookieFactory;
use App\Security\RefreshToken\RefreshTokenExtractor;
use App\Security\RefreshToken\RefreshTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LogoutController
{
    public function __construct(private RefreshTokenManager       $refreshTokenManager,
                                private RefreshTokenExtractor     $refreshTokenExtractor,
                                private RefreshTokenCookieFactory $refreshTokenCookieFactory,
                                private EntityManagerInterface    $entityManager,
    )
    {
    }

    #[Route('/api/v1/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $plainRefreshToken = $this->refreshTokenExtractor->extract($request);

        if ($plainRefreshToken !== null) {
            $this->refreshTokenManager->revoke(plainToken: $plainRefreshToken, reason: 'logout');

            $this->entityManager->flush();
        }

        $response = new Response(null, Response::HTTP_NO_CONTENT);

        $response->headers->setCookie(
            $this->refreshTokenCookieFactory->clear()
        );

        return $response;
    }
}
