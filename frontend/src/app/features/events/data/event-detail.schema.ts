import { z } from 'zod';

import {
  EventStatusSchema,
  EventTypeSchema,
  MyEventRegistrationStatusSchema,
  OptionalMyRegistrationStatusSchema,
  OptionalNullableNumberSchema,
  OptionalNullableStringSchema,
} from './event.schema';



/**
 * Runtime schema for the event detail returned by the backend.
 */
export const EventDetailSchema = z.object({
  id: z.string(),
  title: z.string(),

  description: OptionalNullableStringSchema,
  eventType: EventTypeSchema,
  eventStatus: EventStatusSchema,

  clubId: OptionalNullableStringSchema,
  clubName: OptionalNullableStringSchema,
  location: OptionalNullableStringSchema,

  startsAt: z.string(),
  endsAt: z.string(),
  timezone: z.string(),
  allDay: z.boolean(),

  capacity: OptionalNullableNumberSchema,
  registeredCount: z.number(),

  waitlistEnabled: z.boolean(),
  publicRegistrationEnabled: z.boolean(),

  registrationStartsAt: OptionalNullableStringSchema,
  registrationEndsAt: OptionalNullableStringSchema,

  organizationId: OptionalNullableStringSchema,
  organizationName: OptionalNullableStringSchema,

  myRegistrationStatus: OptionalMyRegistrationStatusSchema,
});

/**
 * Event detail displayed by Angular.
 */
export type EventDetail = z.infer<typeof EventDetailSchema>;
