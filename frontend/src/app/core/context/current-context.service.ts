import {CurrentContextState} from './current-context.model';
import {computed, Service, signal} from '@angular/core';

@Service({})
export class CurrentContextService {

  readonly state = signal<CurrentContextState>({status: 'unknown'});

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
}
