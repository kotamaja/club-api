import {
  defaultEventListQuery,
  EventListPeriod,
  EventListQuery,
  EventListSort,
  EventListStatus,
  EventListType
} from './event-list-query.model';

/**
 * Raw URL query params supported by the event list page.
 */
export type EventListUrlQueryParams = {
  search?: string | null;
  period?: string | null;
  eventStatus?: string | null;
  eventType?: string | null;
  sort?: string | null;
  direction?: string | null;
  page?: string | null;
  perPage?: string | null;
};

/**
 * Raw URL view params supported by the event list page.
 */
export type EventListUrlViewParams = {
  selected?: string | null;
};

const validPeriods: readonly EventListPeriod[] = ['upcoming', 'past', 'all'];

const validStatuses: readonly EventListStatus[] = [
  'all',
  'draft',
  'published',
  'cancelled',
  'archived',
];

const validTypes: readonly EventListType[] = [
  'all',
  'general',
  'course',
  'training',
  'trip',
  'meeting',
  'competition',
];

const validSorts: readonly EventListSort[] = [
  'startsAt',
  'title',
  'location',
  'eventStatus',
  'eventType',
];

const validDirections = ['asc', 'desc'] as const;

/**
 * Converts URL query params to a valid event list query.
 */
export function eventListQueryFromUrl(params: EventListUrlQueryParams): EventListQuery {
  return {
    search: params.search ?? defaultEventListQuery.search,
    period: parseEnum(params.period, validPeriods, defaultEventListQuery.period),
    eventStatus: parseEnum(params.eventStatus, validStatuses, defaultEventListQuery.eventStatus),
    eventType: parseEnum(params.eventType, validTypes, defaultEventListQuery.eventType),
    sort: parseEnum(params.sort, validSorts, defaultEventListQuery.sort),
    direction: parseEnum(params.direction, validDirections, defaultEventListQuery.direction),
    page: parsePositiveInteger(params.page, defaultEventListQuery.page),
    perPage: parsePositiveInteger(params.perPage, defaultEventListQuery.perPage),
  };
}

/**
 * Converts an event list query to compact URL query params.
 *
 * Default values are omitted to keep the URL readable.
 */
export function eventListQueryToUrl(query: EventListQuery): Record<string, string | number | null> {
  const search = query.search.trim();

  return {
    search: search === defaultEventListQuery.search ? null : search,
    period: query.period === defaultEventListQuery.period ? null : query.period,
    eventStatus: query.eventStatus === defaultEventListQuery.eventStatus ? null : query.eventStatus,
    eventType: query.eventType === defaultEventListQuery.eventType ? null : query.eventType,
    sort: query.sort === defaultEventListQuery.sort ? null : query.sort,
    direction: query.direction === defaultEventListQuery.direction ? null : query.direction,
    page: query.page === defaultEventListQuery.page ? null : query.page,
    perPage: query.perPage === defaultEventListQuery.perPage ? null : query.perPage,
  };
}

/**
 * Converts URL query params to event list view state.
 */
export function eventListSelectedIdFromUrl(params: EventListUrlViewParams): string | null {
  const selected = params.selected?.trim();

  if (selected === undefined || selected === '') {
    return null;
  }

  return selected;
}

/**
 * Converts the selected event id to compact URL query params.
 */
export function eventListSelectedIdToUrl(selectedId: string | null): Record<string, string | null> {
  return {
    selected: selectedId,
  };
}

function parseEnum<T extends string>(value: string | null | undefined, validValues: readonly T[], fallback: T): T {
  if (value === null || value === undefined) {
    return fallback;
  }

  return validValues.includes(value as T) ? value as T : fallback;
}

function parsePositiveInteger(value: string | null | undefined, fallback: number): number {
  if (value === null || value === undefined) {
    return fallback;
  }

  const parsed = Number(value);

  if (!Number.isInteger(parsed) || parsed < 1) {
    return fallback;
  }

  return parsed;
}
