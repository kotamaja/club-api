/**
 * Primitive values supported in API query parameters.
 */
export type ApiQueryParamValue =
  | string
  | number
  | boolean
  | readonly (string | number | boolean)[]
  | null
  | undefined;

/**
 * Generic API query parameters.
 *
 * Null and undefined values are ignored when building the final HttpParams.
 */
export type ApiQueryParams = Record<string, ApiQueryParamValue>;
