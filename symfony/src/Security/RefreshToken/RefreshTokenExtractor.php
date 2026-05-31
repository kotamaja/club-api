<?php

namespace App\Security\RefreshToken;

use Symfony\Component\HttpFoundation\Request;

final class RefreshTokenExtractor
{
    public function extract(Request $request): ?string
    {
        $cookieToken = $request->cookies->get(RefreshTokenCookie::NAME);

        $bodyToken = $this->extractFromJsonBody($request);

        if (
            is_string($cookieToken) && trim($cookieToken) !== ''
            && is_string($bodyToken) && trim($bodyToken) !== ''
            && $cookieToken !== $bodyToken
        ) {
            return null;
        }

        if (is_string($cookieToken) && trim($cookieToken) !== '') {
            return $cookieToken;
        }

        if (is_string($bodyToken) && trim($bodyToken) !== '') {
            return $bodyToken;
        }

        return null;
    }

    private function extractFromJsonBody(Request $request): ?string
    {
        if ($request->getContent() === '') {
            return null;
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return null;
        }

        $refreshToken = $payload['refreshToken'] ?? null;

        return is_string($refreshToken) ? $refreshToken : null;
    }
}
