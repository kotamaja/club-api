<?php

namespace App\Security\RefreshToken;

enum RefreshTokenMode: string
{
    case Body = 'body';
    case Cookie = 'cookie';

    case None = 'none';

    public static function fromRequestValue(mixed $value): self
    {
        if (!is_string($value) || trim($value) === '') {
            return self::Body;
        }

        return match (mb_strtolower(trim($value))) {
            self::Cookie->value => self::Cookie,
            self::Body->value => self::Body,
            self::None->value => self::None,
            default => self::Body,
        };
    }

    public function createsRefreshToken(): bool
    {
        return $this !== self::None;
    }

    public function returnsRefreshTokenInBody(): bool
    {
        return $this === self::Body;
    }

    public function usesCookie(): bool
    {
        return $this === self::Cookie;
    }
}
