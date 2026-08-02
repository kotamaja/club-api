import { CurrentUser } from './current-user.model';

/**
 * Represents the current frontend authentication state.
 */
export type AuthState =
  | { status: 'unknown' }
  | { status: 'anonymous' }
  | { status: 'authenticated'; user: CurrentUser };
