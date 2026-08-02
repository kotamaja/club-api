import { computed, inject, Service } from '@angular/core';

import { CurrentContextService } from '../context/current-context.service';
import { Capability } from './capability.model';

/**
 * Provides capability checks for routes, menus and UI actions.
 *
 * Components should ask this service for capabilities instead of checking
 * user roles directly.
 */
@Service()
export class CapabilityService {
  private readonly currentContext = inject(CurrentContextService);

  readonly capabilities = computed(() => this.currentContext.capabilities());

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
