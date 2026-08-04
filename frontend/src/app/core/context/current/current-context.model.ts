import { Capability } from '../../capabilities/capability.model';

/**
 * Minimal organization summary used in the current frontend context.
 */
export type CurrentOrganizationSummary = {
  id: string;
  name: string;
};

/**
 * Minimal club summary used in the current frontend context.
 */
export type CurrentClubSummary = {
  id: string;
  name: string;
};

/**
 * Resolved organization and club context for the connected user.
 *
 * The club can be null while the organization is selected but no club
 * selection endpoint has been connected yet.
 */
export type CurrentContext = {
  organization: CurrentOrganizationSummary;
  club: CurrentClubSummary | null;
  capabilities: Capability[];
};

/**
 * Represents the current organization and club context state.
 */
export type CurrentContextState =
  | { status: 'unknown' }
  | { status: 'missing' }
  | { status: 'resolved'; context: CurrentContext };
