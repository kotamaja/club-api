import { SortDirection } from '../../../shared/list-state/list-query-state.model';

/**
 * UI-level member status filter for the member management list.
 *
 * Values must either map directly to API filters or be translated before
 * sending the request.
 */
export type MemberListStatus = 'all' | 'active' | 'inactive' | 'withoutMembership';

/**
 * Sort fields supported by the member management list.
 *
 * Each value must correspond to a backend-sortable field.
 */
export type MemberListSort = 'lastname' | 'email' | 'createdAt';

/**
 * Query state used by the member management list.
 *
 * This model represents the list state stored in the URL.
 */
export type MemberListQuery = {
  search: string;
  status: MemberListStatus;
  sort: MemberListSort;
  direction: SortDirection;
  page: number;
  perPage: number;
};

/**
 * Default query state for the member management list.
 */
export const defaultMemberListQuery: MemberListQuery = {
  search: '',
  status: 'all',
  sort: 'lastname',
  direction: 'asc',
  page: 1,
  perPage: 20,
};
