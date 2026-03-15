<?php

namespace App\Enum;

enum RelationshipType: string
{
    case LEGAL_GUARDIAN = 'legal_guardian';
    case PARENT = 'parent';

    case OTHER = 'other';
}
