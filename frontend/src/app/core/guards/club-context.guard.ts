import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { CurrentContextService } from '../context/current/current-context.service';

/**
 * Ensures that a current club is selected for routes that require a club
 * context.
 */
export const clubContextGuard: CanActivateFn = (route, state) => {
  const currentContext = inject(CurrentContextService);
  const router = inject(Router);

  const contextState = currentContext.state();

  if (contextState.status !== 'resolved') {
    return router.createUrlTree(['/app']);
  }

  if (contextState.context.club !== null) {
    return true;
  }

  return router.createUrlTree(['/app/select-club'], {
    queryParams: {
      returnUrl: state.url,
    },
  });
};
