<?php

namespace App\Security\RefreshToken;

use App\Entity\RefreshToken;

final readonly class CreatedRefreshToken
{
    public function __construct(public RefreshToken $entity, public string $plainToken)
    {
    }
}
