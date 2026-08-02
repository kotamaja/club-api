import { CurrentUser } from './current-user.model';

/**
 * Result returned after a successful authentication request.
 */
export type LoginResult = {
  accessToken: string;
  user: CurrentUser;
};
