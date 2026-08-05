import { SortDirection } from '../../../shared/list-state/list-query-state.model';
import {EventType} from './event.schema';

/**
 * UI-level period filter for the event list.
 *
 * This value is not sent directly to the API. It must be translated to
 * backend-supported date filters, for example startsAt[after] or startsAt[before].
 */
export type EventListPeriod = 'upcoming' | 'past' | 'all';

/**
 * UI-level event status filter.
 *
 * Values must map to event statuses supported by the API.
 */
export type EventListStatus = 'all' | 'draft' | 'published' | 'cancelled' | 'archived';


/**
 * UI-level event type filter.
 *
 * Values must map to event types supported by the API.
 */
export type EventListType = 'all' | EventType;

/**
 * Sort fields supported by the event management list.
 *
 * Each value must correspond to a backend-sortable field.
 */
export type EventListSort =
  | 'startsAt'
  | 'title'
  | 'location'
  | 'status'
  | 'type';
/**
 * Query state used by the event management list.
 *
 * This model represents the list state stored in the URL. Some values are
 * direct API filters, while others are UI-level filters translated before
 * sending the request.
 */
export type EventListQuery = {
  search: string;
  period: EventListPeriod;
  status: EventListStatus;
  type: EventListType;
  sort: EventListSort;
  direction: SortDirection;
  page: number;
  perPage: number;
};

/**
 * Default query state for the event management list.
 */
export const defaultEventListQuery: EventListQuery = {
  search: '',
  period: 'upcoming',
  status: 'all',
  type: 'all',
  sort: 'startsAt',
  direction: 'asc',
  page: 1,
  perPage: 20,
};
