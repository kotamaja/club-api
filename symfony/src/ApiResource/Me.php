<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Me\MeProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
            provider: MeProvider::class,
        ),
    ],
)]
final readonly class Me
{
    public function __construct(
        public string $id,
        public string $email,
        public string $status,
        public array $roles,
    ) {
    }
}
