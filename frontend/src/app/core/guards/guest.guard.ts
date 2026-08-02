import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { SessionService } from '../auth/session.service';

/**
 * Protects public authentication routes.
 *
 * Authenticated users are redirected to the connected application.
 */
export const guestGuard: CanActivateFn = () => {
  const session = inject(SessionService);
  const router = inject(Router);

  if (session.isAuthenticated()) {
    return router.createUrlTree(['/app']);
  }

  return true;
};
