import { z } from 'zod';

/**
 * Registration status values exposed by the event registration API.
 */
export const EventRegistrationStatusSchema = z.enum([
  'registered',
  'waitlisted',
  'cancelled',
]);

export type EventRegistrationStatus = z.infer<typeof EventRegistrationStatusSchema>;
