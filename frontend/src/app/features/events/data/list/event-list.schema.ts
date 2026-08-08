import { z } from 'zod';

import { ApiPage } from '../../../../core/api/api-page.model';
import {
  EventStatusSchema,
  EventTypeSchema,
  OptionalMyRegistrationStatusSchema,
  OptionalNullableNumberSchema,
  OptionalNullableStringSchema,
} from '../shared/event.schema';

/**
 * Runtime schema for one event item in the management list.
 */
export const EventListItemSchema = z.object({
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
 * Event item displayed in the management event list.
 */
export type EventListItem = z.infer<typeof EventListItemSchema>;

/**
 * Paginated event list response used by Angular.
 */
export type EventListPage = ApiPage<EventListItem>;
