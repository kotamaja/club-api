import { EventRegistrationStatus } from '../shared/event-registration.schema';

/**
 * Human-readable labels for event registration statuses.
 */
export const eventRegistrationStatusLabels: Record<EventRegistrationStatus, string> = {
  registered: 'Inscrit',
  waitlisted: 'En liste d’attente',
  cancelled: 'Annulé',
};
