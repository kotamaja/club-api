<?php

namespace App\Core\Enum;

enum Feature: string
{
    case EventBasic = 'event.basic';
    case EventWaitlist = 'event.waitlist';
    case EventMultiSession = 'event.multi_session';
    case EventCustomForm = 'event.custom_form';
    case EventManualSelection = 'event.manual_selection';
    case EventGroupVisibility = 'event.group_visibility';
    case EventGroupEligibility = 'event.group_eligibility';
    case EventInterclub = 'event.interclub';
    case EventAttendanceTracking = 'event.attendance_tracking';
    case EventDocuments = 'event.documents';
}
