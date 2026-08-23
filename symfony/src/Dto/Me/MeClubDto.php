<?php

namespace App\Dto\Me;

final readonly class MeClubDto
{
    public function __construct(
        public string $clubId,
        public string $clubName,
    ) {
    }
}
