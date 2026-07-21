<?php

namespace App\Mapper;

interface MapperRegistryInterface
{
    public function map(mixed $source, mixed $targetOrClass): mixed;

}
