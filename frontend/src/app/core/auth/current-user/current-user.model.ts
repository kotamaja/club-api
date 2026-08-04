import { UserStatus } from './me.model';

/**
 * Authenticated user representation stored in the frontend session.
 */
export type CurrentUser = {
  id: string;
  email: string;
  displayName: string;
  status: UserStatus;
  roles: string[];
};
