import { EventCreatePayload } from '../write/event-create.model';
import { EventDetail } from '../detail/event-detail.schema';
import { EventEditForm, EventFormBase } from './event-form.model';
import { EventUpdatePayload } from '../write/event-update.model';

/**
 * Creates the default form used by the event creation page.
 */
export function createDefaultEventForm(): EventFormBase {
  return {
    title: '',
    description: '',
    eventType: 'general',
    clubId: null,
    location: '',
    startsAt: '',
    endsAt: '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'Europe/Zurich',
    allDay: false,
    capacity: '',
    waitlistEnabled: false,
    publicRegistrationEnabled: false,
    registrationStartsAt: '',
    registrationEndsAt: '',
  };
}

/**
 * Creates the edit form from an event detail.
 */
export function createEventEditForm(event: EventDetail): EventEditForm {
  return {
    title: event.title,
    description: event.description ?? '',
    eventType: event.eventType,
    eventStatus: event.eventStatus,
    clubId: event.clubId,
    location: event.location ?? '',
    startsAt: toDateTimeLocalValue(event.startsAt),
    endsAt: toDateTimeLocalValue(event.endsAt),
    timezone: event.timezone,
    allDay: event.allDay,
    capacity: event.capacity === null ? '' : String(event.capacity),
    waitlistEnabled: event.waitlistEnabled,
    publicRegistrationEnabled: event.publicRegistrationEnabled,
    registrationStartsAt: toDateTimeLocalValue(event.registrationStartsAt),
    registrationEndsAt: toDateTimeLocalValue(event.registrationEndsAt),
  };
}

/**
 * Creates an API payload for event creation.
 */
export function createEventCreatePayload(form: EventFormBase): EventCreatePayload {
  return {
    title: form.title.trim(),
    description: toNullableString(form.description),
    eventType: form.eventType,
    clubId: form.clubId,
    location: toNullableString(form.location),
    startsAt: toApiDateTime(form.startsAt, null),
    endsAt: toApiDateTime(form.endsAt, null),
    timezone: form.timezone,
    allDay: form.allDay,
    capacity: toNullableInteger(form.capacity),
    waitlistEnabled: form.waitlistEnabled,
    publicRegistrationEnabled: form.publicRegistrationEnabled,
    registrationStartsAt: toNullableApiDateTime(form.registrationStartsAt, null),
    registrationEndsAt: toNullableApiDateTime(form.registrationEndsAt, null),
  };
}

/**
 * Creates an API payload for event update.
 */
export function createEventUpdatePayload(event: EventDetail, form: EventEditForm): EventUpdatePayload {
  return {
    title: form.title.trim(),
    description: toNullableString(form.description),
    eventType: form.eventType,
    eventStatus: form.eventStatus,
    clubId: form.clubId,
    location: toNullableString(form.location),
    startsAt: toApiDateTime(form.startsAt, event.startsAt),
    endsAt: toApiDateTime(form.endsAt, event.endsAt),
    timezone: form.timezone,
    allDay: form.allDay,
    capacity: toNullableInteger(form.capacity),
    waitlistEnabled: form.waitlistEnabled,
    publicRegistrationEnabled: form.publicRegistrationEnabled,
    registrationStartsAt: toNullableApiDateTime(form.registrationStartsAt, event.registrationStartsAt ?? event.startsAt),
    registrationEndsAt: toNullableApiDateTime(form.registrationEndsAt, event.registrationEndsAt ?? event.startsAt),
  };
}

function toNullableString(value: string): string | null {
  const trimmed = value.trim();

  return trimmed === '' ? null : trimmed;
}

function toNullableInteger(value: string): number | null {
  const trimmed = value.trim();

  if (trimmed === '') {
    return null;
  }

  return Number(trimmed);
}

function toDateTimeLocalValue(value: string | null): string {
  if (value === null) {
    return '';
  }

  return value.slice(0, 16);
}

function toNullableApiDateTime(value: string, originalValue: string | null): string | null {
  if (value === '') {
    return null;
  }

  return toApiDateTime(value, originalValue);
}

/**
 * Converts a datetime-local value to an ISO-like datetime.
 *
 * This first version preserves the original UTC offset when possible. For new
 * events, the browser's current offset is used.
 */
function toApiDateTime(value: string, originalValue: string | null): string {
  const offset = extractOffset(originalValue) ?? currentOffset();

  return `${value}:00${offset}`;
}

function extractOffset(value: string | null): string | null {
  if (value === null) {
    return null;
  }

  const match = value.match(/([+-]\d{2}:\d{2}|Z)$/);

  return match?.[1] ?? null;
}

function currentOffset(): string {
  const offsetMinutes = -new Date().getTimezoneOffset();
  const sign = offsetMinutes >= 0 ? '+' : '-';
  const absoluteMinutes = Math.abs(offsetMinutes);
  const hours = String(Math.floor(absoluteMinutes / 60)).padStart(2, '0');
  const minutes = String(absoluteMinutes % 60).padStart(2, '0');

  return `${sign}${hours}:${minutes}`;
}
