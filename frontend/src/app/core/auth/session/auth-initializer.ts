import { inject, provideAppInitializer } from '@angular/core';
import { firstValueFrom } from 'rxjs';

import { AuthService } from '../services/auth.service';

/**
 * Restores the authenticated frontend session when the application starts.
 *
 * The initializer tries to refresh the JWT access token using the backend
 * refresh-token cookie before the router guards make their first decision.
 */
export function provideAuthInitializer() {
  return provideAppInitializer(() => {
    const auth = inject(AuthService);

    return firstValueFrom(auth.restoreSession());
  });
}
