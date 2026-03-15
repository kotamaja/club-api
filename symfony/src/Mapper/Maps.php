<?php

namespace App\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Maps
{
    public function __construct(
        public string $source,
        public string $target,
    ) {}
}
