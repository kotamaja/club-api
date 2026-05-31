<?php

namespace App\Security\RefreshToken;

use Symfony\Component\HttpFoundation\Cookie;

final class RefreshTokenCookieFactory
{
    public function create(string $plainRefreshToken, \DateTimeImmutable $expiresAt): Cookie
    {
        return Cookie::create(RefreshTokenCookie::NAME)
            ->withValue($plainRefreshToken)
            ->withExpires($expiresAt)
            ->withPath(RefreshTokenCookie::PATH)
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }

    public function clear(): Cookie
    {
        return Cookie::create(RefreshTokenCookie::NAME)
            ->withValue('')
            ->withExpires(new \DateTimeImmutable('-1 hour'))
            ->withPath(RefreshTokenCookie::PATH)
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }
}
