import { z } from 'zod';

/**
 * Runtime schema for event types returned by the backend.
 */
export const EventTypeSchema = z.enum([
  'general',
  'course',
  'training',
  'trip',
  'meeting',
  'competition',
]);

/**
 * Runtime schema for event statuses returned by the backend.
 */
export const EventStatusSchema = z.enum([
  'draft',
  'published',
  'cancelled',
  'archived',
]);

/**
 * Runtime schema for the current user's event registration status.
 */
export const MyEventRegistrationStatusSchema = z.enum([
  'registered',
  'waitlisted',
  'cancelled',
]).nullable();

/**
 * Normalizes an optional nullable string field to null.
 */
export const OptionalNullableStringSchema = z.string().nullish().transform((value) => value ?? null);

/**
 * Normalizes an optional nullable number field to null.
 */
export const OptionalNullableNumberSchema = z.number().nullish().transform((value) => value ?? null);

/**
 * Normalizes an optional nullable registration status field to null.
 */
export const OptionalMyRegistrationStatusSchema = MyEventRegistrationStatusSchema.nullish().transform((value) => value ?? null);

/**
 * Event type supported by the backend.
 */
export type EventType = z.infer<typeof EventTypeSchema>;

/**
 * Event status supported by the backend.
 */
export type EventStatus = z.infer<typeof EventStatusSchema>;

/**
 * Registration status of the current user for an event.
 */
export type MyEventRegistrationStatus = z.infer<typeof MyEventRegistrationStatusSchema>;
