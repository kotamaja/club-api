<?php

namespace App\Security\RefreshToken;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;

final class RefreshTokenCookieFactory
{

    public function __construct(#[Autowire(env: 'REFRESH_TOKEN_COOKIE_SECURE')] private bool $secure)
    {
    }

    public function create(string $plainRefreshToken, \DateTimeImmutable $expiresAt): Cookie
    {
        return Cookie::create(RefreshTokenCookie::NAME)
            ->withValue($plainRefreshToken)
            ->withExpires($expiresAt)
            ->withPath(RefreshTokenCookie::PATH)
            ->withSecure($this->secure)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }

    public function clear(): Cookie
    {
        return Cookie::create(RefreshTokenCookie::NAME)
            ->withValue('')
            ->withExpires(new \DateTimeImmutable('-1 hour'))
            ->withPath(RefreshTokenCookie::PATH)
            ->withSecure($this->secure)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }
}
