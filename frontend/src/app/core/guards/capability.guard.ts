import { CanActivateChildFn } from '@angular/router';

/**
 * Protects child routes according to the required capabilities.
 *
 * Temporary V1 skeleton: always allows navigation until capabilities are
 * returned by the API and checked by CapabilityService.
 */
export const capabilityGuard: CanActivateChildFn = () => {
  return true;
};
