/**
 * Generic API error representation used by the frontend.
 *
 * This model can later be aligned with the exact error payload returned by
 * the Symfony backend.
 */
export type ApiError = {
  status: number;
  message: string;
  details?: unknown;
};
