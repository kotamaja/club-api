import { computed, inject, Service, signal } from '@angular/core';
import { catchError, map, Observable, of, tap } from 'rxjs';

import { OrganizationAccess } from './organization-access.model';
import { OrganizationAccessService } from './organization-access.service';
import { OrganizationAccessStoreState } from './organization-access-store.model';

/**
 * Stores the organizations available to the authenticated user.
 *
 * This avoids loading /me/organizations several times during login,
 * session restoration and organization selection.
 */
@Service()
export class OrganizationAccessStoreService {
  private readonly organizationAccess = inject(OrganizationAccessService);

  readonly state = signal<OrganizationAccessStoreState>({ status: 'unknown' });

  readonly organizations = computed(() => {
    const state = this.state();

    return state.status === 'loaded' ? state.organizations : [];
  });

  readonly enabledOrganizations = computed(() => {
    return this.organizations().filter((organization) => organization.enabled);
  });

  load(): Observable<OrganizationAccess[]> {
    const state = this.state();

    if (state.status === 'loaded') {
      return of(state.organizations);
    }

    this.state.set({ status: 'loading' });

    return this.organizationAccess.getOrganizations().pipe(
      tap((organizations) => {
        this.state.set({
          status: 'loaded',
          organizations,
        });
      }),
      catchError(() => {
        this.state.set({
          status: 'error',
          message: 'Impossible de charger les organisations disponibles.',
        });

        return of([]);
      }),
    );
  }

  reload(): Observable<OrganizationAccess[]> {
    this.state.set({ status: 'unknown' });

    return this.load();
  }

  clear(): void {
    this.state.set({ status: 'unknown' });
  }
}
