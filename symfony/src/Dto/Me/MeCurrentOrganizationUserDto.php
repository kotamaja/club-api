<?php

namespace App\Dto\Me;

final readonly class MeCurrentOrganizationUserDto
{
    public function __construct(
        public string $id,
        public array $roles,
        public bool $enabled,
    ) {
    }
}
