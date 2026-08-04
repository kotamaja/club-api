/**
 * Status values supported by backend users.
 */
export type UserStatus = 'invited' | 'active' | 'disabled';

/**
 * Current authenticated user returned by the backend.
 */
export type Me = {
  id: string;
  email: string;
  status: UserStatus;
  roles: string[];
};
