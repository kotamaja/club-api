<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Me\MeClubDto;
use App\State\Me\MeClubProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/me/organizations/{organizationId}/clubs',
            paginationEnabled: false,
            output: MeClubDto::class,
            provider: MeClubProvider::class,
        ),
    ],
    routePrefix: '/v1',
)]
final class MeClub
{
}
