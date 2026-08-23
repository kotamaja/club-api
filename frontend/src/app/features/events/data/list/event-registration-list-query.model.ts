import { EventRegistrationStatus } from '../shared/event-registration.schema';

export type EventRegistrationListSort =
  | 'personLastname'
  | 'status'
  | 'requestedAt'
  | 'cancelledAt';

export type EventRegistrationListDirection = 'asc' | 'desc';

export type EventRegistrationListQuery = {
  page: number;
  perPage: number;
  status: EventRegistrationStatus | 'all';
  sort: EventRegistrationListSort;
  direction: EventRegistrationListDirection;
};

export const defaultEventRegistrationListQuery: EventRegistrationListQuery = {
  page: 1,
  perPage: 30,
  status: 'all',
  sort: 'requestedAt',
  direction: 'desc',
};
