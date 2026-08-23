import { z } from 'zod';

import { ApiPage } from '../../../../core/api/api-page.model';
import { EventRegistrationStatusSchema } from '../shared/event-registration.schema';

/**
 * Event registration item displayed in the management registration list.
 */
export const EventRegistrationListItemSchema = z.object({
  id: z.string(),
  eventId: z.string(),
  eventTitle: z.string(),
  personId: z.string(),
  personFirstname: z.string(),
  personLastname: z.string(),
  membershipId: z.string().nullable().optional().transform((value) => value ?? null),
  status: EventRegistrationStatusSchema,
  requestedAt: z.string(),
  cancelledAt: z.string().nullable().optional().transform((value) => value ?? null),
  note: z.string().nullable().optional().transform((value) => value ?? null),
});

export type EventRegistrationListItem = z.infer<typeof EventRegistrationListItemSchema>;

const EventRegistrationListApiPageSchema = z.object({
  items: z.array(EventRegistrationListItemSchema),
  pagination: z.object({
    page: z.number(),
    itemsPerPage: z.number(),
    lastPage: z.number(),
    totalItems: z.number(),
  }),
});

/**
 * Parses the paginated event registration list returned by API Platform.
 */
export const EventRegistrationListPageSchema = EventRegistrationListApiPageSchema.transform((value): ApiPage<EventRegistrationListItem> => {
  return {
    items: value.items,
    page: value.pagination.page,
    perPage: value.pagination.itemsPerPage,
    totalItems: value.pagination.totalItems,
  };
});

export type EventRegistrationListPage = z.infer<typeof EventRegistrationListPageSchema>;
