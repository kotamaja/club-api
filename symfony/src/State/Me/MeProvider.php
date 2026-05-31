<?php

namespace App\State\Me;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Me\MeViewDto;

class MeProvider implements ProviderInterface
{


    public function __construct()
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return new MeViewDto();
    }
}
