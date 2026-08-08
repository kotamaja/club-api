import {EventStatus, EventType} from '../shared/event.schema';

/**
 * Shared editable form state for event create and edit pages.
 *
 * Form values keep user input as strings when needed. API payloads are created
 * later by mappers.
 */
export type EventFormBase = {
  title: string;
  description: string;
  eventType: EventType;
  clubId: string | null;
  location: string;
  startsAt: string;
  endsAt: string;
  timezone: string;
  allDay: boolean;
  capacity: string;
  waitlistEnabled: boolean;
  publicRegistrationEnabled: boolean;
  registrationStartsAt: string;
  registrationEndsAt: string;
};

/**
 * Editable form state for updating an existing event.
 *
 * Event status is editable only in update mode. New events are created as draft
 * by the backend.
 */
export type EventEditForm = EventFormBase & {
  eventStatus: EventStatus;
};

/**
 * Validation error displayed by event forms.
 */
export type EventFormValidationError = {
  field: keyof EventFormBase | 'eventStatus' | 'form';
  message: string;
};
