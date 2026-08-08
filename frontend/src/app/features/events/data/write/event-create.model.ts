import { EventType } from '../shared/event.schema';

/**
 * Payload used to create an event.
 *
 * Event status is intentionally omitted. The backend creates new events as
 * draft.
 */
export type EventCreatePayload = {
  title: string;
  description: string | null;
  eventType: EventType;
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
