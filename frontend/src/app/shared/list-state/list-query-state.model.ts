/**
 * Sort direction used by list pages.
 */
export type SortDirection = 'asc' | 'desc';

/**
 * Minimal shared query state for list pages.
 *
 * Feature-specific lists should define their own query model and only reuse
 * the common concepts that make sense for them.
 */
export type ListQueryState = {
  search?: string;
  sort?: string;
  direction?: SortDirection;
  page?: number;
  perPage?: number;
};
