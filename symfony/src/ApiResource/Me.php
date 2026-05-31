<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Me\MeViewDto;
use App\State\Me\MeProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/auth/me',
            normalizationContext: [
                'skip_null_values' => false,
            ],
            security: "is_granted('ROLE_USER')",
            output: MeViewDto::class,
            provider: MeProvider::class,
        )
    ],
    routePrefix: '',
)]
final class Me
{

}
