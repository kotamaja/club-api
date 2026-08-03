/**
 * Command sent to the backend for password authentication.
 */
export type PasswordLoginCommand = {
  email: string;
  password: string;
};

/**
 * Response returned by the backend after successful password authentication.
 *
 * The backend returns a JWT access token and a refresh token.
 */
export type LoginResponse = {
  token: string;
  refreshToken: string;
};
