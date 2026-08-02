import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { CurrentContextService } from '../context/current-context.service';

/**
 * Ensures that a usable organization and club context is available.
 *
 * Until the real context resolver is connected to the API, unresolved context
 * states redirect to the organization selection page.
 */
export const contextGuard: CanActivateFn = () => {
  const currentContext = inject(CurrentContextService);
  const router = inject(Router);

  const state = currentContext.state();

  console.log (state)

  if (state.status === 'resolved') {
    return true;
  }

  return router.createUrlTree(['/app/select-organization']);
};
