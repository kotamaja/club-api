<?php

namespace App\Dto\Me;

final readonly class MeCurrentOrganizationDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
    ) {
    }
}
