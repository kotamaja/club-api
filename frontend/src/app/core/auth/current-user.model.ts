/**
 * Minimal authenticated user representation used by the frontend session.
 */
export type CurrentUser = {
  id: string;
  email: string;
  displayName: string;
};
