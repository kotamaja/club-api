/**
 * Refresh token delivery mode supported by the backend.
 */
export type RefreshTokenMode = 'body' | 'cookie' | 'none';

/**
 * Command sent to the backend for password authentication.
 */
export type PasswordLoginCommand = {
  email: string;
  password: string;
  refreshTokenMode: RefreshTokenMode;
};

/**
 * Response returned by the backend after successful web authentication.
 *
 * In cookie mode, the refresh token is not returned in the response body.
 */
export type LoginResponse = {
  token: string;
};

/**
 * Response returned after refreshing the JWT access token.
 *
 * In cookie mode, Angular receives only the new access token.
 */
export type RefreshResponse = {
  token: string;
};
