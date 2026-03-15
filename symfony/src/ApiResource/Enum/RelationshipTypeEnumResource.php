<?php

namespace App\ApiResource\Enum;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Enum\EnumChoiceDto;
use App\Enum\RelationshipType;
use App\State\Enum\EnumCollectionProvider;

#[ApiResource(operations: [
    new GetCollection(
        uriTemplate: '/enums/relationship-types',
        cacheHeaders: [
            'max_age' => 86400,
            'shared_max_age' => 86400,
        ],
        paginationEnabled: false,
        output: EnumChoiceDto::class,
        provider: EnumCollectionProvider::class,
        extraProperties: [
            'app.enum_class' => RelationshipType::class,
            'app.enum_i18n_domain' => 'relationship_type',
        ],
    )],
    routePrefix: '/v1')]
final class RelationshipTypeEnumResource
{

}
