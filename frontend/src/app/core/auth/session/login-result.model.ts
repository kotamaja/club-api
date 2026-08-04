import { CurrentUser } from '../current-user/current-user.model';

/**
 * Result exposed by AuthService after successful authentication.
 *
 * The refresh token is intentionally absent because the web frontend uses
 * refreshTokenMode="cookie".
 */
export type LoginResult = {
  accessToken: string;
  user: CurrentUser;
};
