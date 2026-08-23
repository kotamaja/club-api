import { computed, inject, Service, signal } from '@angular/core';
import { catchError, Observable, of, tap } from 'rxjs';

import { ClubAccess } from './club-access.model';
import { ClubAccessService } from './club-access.service';
import { ClubAccessStoreState } from './club-access-store.model';

/**
 * Stores the clubs available to the authenticated user for one organization.
 */
@Service()
export class ClubAccessStoreService {
  private readonly clubAccess = inject(ClubAccessService);

  readonly state = signal<ClubAccessStoreState>({ status: 'unknown' });

  readonly clubs = computed(() => {
    const state = this.state();

    return state.status === 'loaded' ? state.clubs : [];
  });

  load(organizationId: string): Observable<ClubAccess[]> {
    const state = this.state();

    if (state.status === 'loaded' && state.organizationId === organizationId) {
      return of(state.clubs);
    }

    this.state.set({ status: 'loading', organizationId });

    return this.clubAccess.getClubs(organizationId).pipe(
      tap((clubs) => {
        this.state.set({
          status: 'loaded',
          organizationId,
          clubs,
        });
      }),
      catchError(() => {
        this.state.set({
          status: 'error',
          organizationId,
          message: 'Impossible de charger les clubs disponibles.',
        });

        return of([]);
      }),
    );
  }

  reload(organizationId: string): Observable<ClubAccess[]> {
    this.state.set({ status: 'unknown' });

    return this.load(organizationId);
  }

  clear(): void {
    this.state.set({ status: 'unknown' });
  }
}
