<?php

namespace App\Enum;

enum UserStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Disabled = 'disabled';
}
