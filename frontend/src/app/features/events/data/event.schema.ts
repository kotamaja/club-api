import { z } from 'zod';

import { ApiPage } from '../../../core/api/api-page.model';

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
const OptionalNullableStringSchema = z.string().nullish().transform((value) => value ?? null);

/**
 * Normalizes an optional nullable number field to null.
 */
const OptionalNullableNumberSchema = z.number().nullish().transform((value) => value ?? null);

/**
 * Normalizes an optional nullable registration status field to null.
 */
const OptionalMyRegistrationStatusSchema = MyEventRegistrationStatusSchema.nullish().transform((value) => value ?? null);

/**
 * Runtime schema for one event item in the management list.
 */
export const EventListItemSchema = z.object({
  id: z.string(),
  title: z.string(),

  description: OptionalNullableStringSchema,
  type: EventTypeSchema,
  status: EventStatusSchema,

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
 * Runtime schema for the backend pagination object.
 */
const EventListPaginationSchema = z.object({
  page: z.number(),
  itemsPerPage: z.number(),
  lastPage: z.number(),
  totalItems: z.number(),
});

/**
 * Runtime schema for the backend event list response.
 *
 * The backend returns pagination under a nested "pagination" key.
 * The schema transforms it into the shared ApiPage shape used by Angular.
 */
export const EventListPageSchema = z.object({
  items: z.array(EventListItemSchema),
  pagination: EventListPaginationSchema,
}).transform((response) => ({
  items: response.items,
  page: response.pagination.page,
  perPage: response.pagination.itemsPerPage,
  totalItems: response.pagination.totalItems,
}));

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

/**
 * Event item displayed in the management event list.
 */
export type EventListItem = z.infer<typeof EventListItemSchema>;

/**
 * Paginated event list response used by Angular.
 */
export type EventListPage = ApiPage<EventListItem>;
