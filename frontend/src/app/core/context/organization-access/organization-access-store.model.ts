import { OrganizationAccess } from './organization-access.model';

/**
 * State used while loading the organizations available to the current user.
 */
export type OrganizationAccessStoreState =
  | { status: 'unknown' }
  | { status: 'loading' }
  | { status: 'loaded'; organizations: OrganizationAccess[] }
  | { status: 'error'; message: string };
