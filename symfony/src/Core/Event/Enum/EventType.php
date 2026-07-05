<?php

namespace App\Core\Event\Enum;


enum EventType: string
{
    case General = 'general';
    case Course = 'course';
    case Training = 'training';
    case Trip = 'trip';
    case Meeting = 'meeting';
    case Competition = 'competition';
}
