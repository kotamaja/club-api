import { CanActivateFn } from '@angular/router';

/**
 * Ensures that the current organization and club context is available.
 *
 * Temporary V1 skeleton: always allows navigation until the context resolver
 * is connected to the API.
 */
export const contextGuard: CanActivateFn = () => {
  return true;
};
