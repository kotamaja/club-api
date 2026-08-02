import { Capability } from '../capabilities/capability.model';

/**
 * Defines how route capabilities should be evaluated.
 *
 * "all" means the user must have every listed capability.
 * "any" means the user must have at least one listed capability.
 */
export type RouteCapabilityMode = 'all' | 'any';

/**
 * Capability metadata supported by protected routes.
 */
export type RouteCapabilityData = {
  capabilities?: Capability[];
  capabilityMode?: RouteCapabilityMode;
};
