/**
 * Generic paginated API collection response.
 *
 * Ramalo collection endpoints return this shape when pagination=true.
 */
export type ApiPage<T> = {
  items: T[];
  page: number;
  perPage: number;
  totalItems: number;
};
