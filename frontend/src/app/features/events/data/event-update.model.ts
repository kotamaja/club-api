import { EventStatus, EventType } from './event.schema';

/**
 * Payload used to update an event.
 *
 * The frontend sends the editable event fields to the API. Values should match
 * the backend Event patch DTO.
 */
export type EventUpdatePayload = {
  title: string;
  description: string | null;
  eventType: EventType;
  eventStatus: EventStatus;
  clubId: string | null;
  location: string | null;
  startsAt: string;
  endsAt: string;
  timezone: string;
  allDay: boolean;
  capacity: number | null;
  waitlistEnabled: boolean;
  publicRegistrationEnabled: boolean;
  registrationStartsAt: string | null;
  registrationEndsAt: string | null;
};
