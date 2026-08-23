import { ClubAccess } from './club-access.model';

/**
 * State used while loading the clubs available in one organization.
 */
export type ClubAccessStoreState =
  | { status: 'unknown' }
  | { status: 'loading'; organizationId: string }
  | { status: 'loaded'; organizationId: string; clubs: ClubAccess[] }
  | { status: 'error'; organizationId: string; message: string };
