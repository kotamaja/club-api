<?php

namespace App\Core\Enum;

enum Limit: string
{
    case MaxClubs = 'max_clubs';
    case MaxMembers = 'max_members';
    case MaxActiveEvents = 'max_active_events';
    case MaxEventParticipants = 'max_event_participants';
}
