import { computed, Service, signal } from '@angular/core';

import { AuthState } from './auth-state.model';
import { CurrentUser } from './current-user.model';

/**
 * Holds the current frontend session state.
 *
 * This service is the single place where the application knows whether the
 * user is anonymous, authenticated, or still being resolved.
 */
@Service()
export class SessionService {
  readonly state = signal<AuthState>({ status: 'unknown' });

  readonly isAuthenticated = computed(() => this.state().status === 'authenticated');

  readonly currentUser = computed<CurrentUser | null>(() => {
    const state = this.state();

    return state.status === 'authenticated' ? state.user : null;
  });

  setAuthenticated(user: CurrentUser): void {
    this.state.set({
      status: 'authenticated',
      user,
    });
  }

  setAnonymous(): void {
    this.state.set({ status: 'anonymous' });
  }

  reset(): void {
    this.state.set({ status: 'unknown' });
  }
}
