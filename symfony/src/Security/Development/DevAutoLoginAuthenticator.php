<?php

namespace App\Security\Development;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class DevAutoLoginAuthenticator extends AbstractAuthenticator
{
    private const EXCLUDED_PATHS = [
        '/api/v1/auth/login',
        '/api/v1/auth/refresh',
        '/api/v1/auth/logout',
        '/api',
        '/api/',
        '/api/docs',
        '/api/contexts',
    ];

    public function __construct( #[Autowire(env: 'DEV_AUTO_LOGIN_USER')]  private readonly string $userIdentifier,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->headers->get('X-Disable-Dev-Auto-Login') === '1') {
            return false;
        }

        if ($request->headers->has('Authorization')) {
            return false;
        }

        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api/')) {
            return false;
        }

        if (in_array($path, [
            '/api/v1/auth/login',
            '/api/v1/auth/refresh',
            '/api/v1/auth/logout',
            '/api',
            '/api/',
        ], true)) {
            return false;
        }

        if (
            str_starts_with($path, '/api/docs')
            || str_starts_with($path, '/api/contexts')
        ) {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): Passport
    {
        return new SelfValidatingPassport(
            new UserBadge($this->userIdentifier),
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): ?Response {
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception,
    ): ?Response {
        return new JsonResponse(
            [
                'title' => 'Development authentication failed',
                'detail' => sprintf(
                    'Unable to authenticate development user "%s".',
                    $this->userIdentifier,
                ),
            ],
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
