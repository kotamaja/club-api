<?php

namespace App\Dto\Me;

final readonly class MePersonSummaryDto
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
    ) {
    }
}
