import { CurrentUser } from './current-user.model';

/**
 * Result exposed by AuthService after successful authentication.
 */
export type LoginResult = {
  accessToken: string;
  user: CurrentUser;
};
