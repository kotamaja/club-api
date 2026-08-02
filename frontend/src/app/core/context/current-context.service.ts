import { computed, Service, signal } from '@angular/core';

import { CurrentContext, CurrentContextState } from './current-context.model';

/**
 * Holds the current organization and club context.
 *
 * The context determines which club the user is working in and which
 * capabilities are available in that context.
 */
@Service()
export class CurrentContextService {
  readonly state = signal<CurrentContextState>({ status: 'unknown' });

  readonly isResolved = computed(() => this.state().status === 'resolved');

  readonly currentOrganization = computed(() => {
    const state = this.state();

    return state.status === 'resolved' ? state.context.organization : null;
  });

  readonly currentClub = computed(() => {
    const state = this.state();

    return state.status === 'resolved' ? state.context.club : null;
  });

  readonly capabilities = computed(() => {
    const state = this.state();

    return state.status === 'resolved' ? state.context.capabilities : [];
  });

  setContext(context: CurrentContext): void {
    this.state.set({
      status: 'resolved',
      context,
    });
  }

  setMissing(): void {
    this.state.set({ status: 'missing' });
  }

  reset(): void {
    this.state.set({ status: 'unknown' });
  }

  setDevelopmentContext(): void {

    console.log("setDevelopmentContext")
    this.setContext({
      organization: {
        id: 'dev-organization',
        name: 'Ramalo Dev',
      },
      club: {
        id: 'dev-club',
        name: 'Club de développement',
      },
      capabilities: [
        'canAccessMemberArea',
        'canAccessManagementArea',
        'canManageMembers',
        'canManageMemberships',
        'canManageEvents',
        'canManageEventRegistrations',
        'canReviewPublicRegistrationRequests',
        'canManageClubSettings',
      ],
    });
  }
}
