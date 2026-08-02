import {computed, inject, Injectable, Service} from '@angular/core';
import {CurrentContextService} from '../context/current-context.service';
import {Capability} from './capability.model';

@Service()
export class CapabilityService {
  private readonly currentContext = inject(CurrentContextService);
  readonly capabilities = computed(() => {
    const state = this.currentContext.state();
    if (state.status !== 'resolved') {
      return [];
    }
    return state.context.capabilities;
  });

  can(capability: Capability): boolean {
    return this.capabilities().includes(capability);
  }

  canAny(capabilities: Capability[]): boolean {
    return capabilities.some((capability) => this.can(capability));
  }

  canAll(capabilities: Capability[]): boolean {
    return capabilities.every((capability) => this.can(capability));
  }
}
