import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { CurrentContextService } from '../context/current/current-context.service';

/**
 * Ensures that a current club is selected for routes that require a club
 * context.
 */
export const clubContextGuard: CanActivateFn = () => {
  const currentContext = inject(CurrentContextService);
  const router = inject(Router);

  const state = currentContext.state();

  if (state.status !== 'resolved') {
    return router.createUrlTree(['/app']);
  }

  if (state.context.club !== null) {
    return true;
  }

  return router.createUrlTree(['/app/select-club']);
};
