<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Me\MeCurrentContextDto;
use App\State\Me\MeCurrentContextProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me/current-context',
            output: MeCurrentContextDto::class,
            provider: MeCurrentContextProvider::class,
        ),
    ],
    routePrefix: '/v1',
)]
class MeCurrentContext
{
}
