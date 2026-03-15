<?php

namespace App\Mapper;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.mapper')]
interface CustomMapperInterface
{
    /**
     * @param object $source
     * @param object|null $target (optionnel si tu veux mapper dans un objet existant)
     */
    public function map(mixed $source, mixed $target = null): mixed;


}
