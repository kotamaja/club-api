import { inject } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivateChildFn, Router } from '@angular/router';

import { CapabilityService } from '../capabilities/capability.service';
import { Capability } from '../capabilities/capability.model';
import { RouteCapabilityMode } from '../routing/route-capability.model';

/**
 * Protects child routes according to the capabilities declared in route data.
 *
 * Capabilities are collected from the full route tree so parent route
 * requirements and child route requirements are both respected.
 */
export const capabilityGuard: CanActivateChildFn = (childRoute: ActivatedRouteSnapshot) => {
  const capability = inject(CapabilityService);
  const router = inject(Router);

  const capabilities = collectCapabilities(childRoute);
  const mode = collectCapabilityMode(childRoute);

  if (capabilities.length === 0) {
    return true;
  }

  const isAllowed = mode === 'any'
    ? capability.canAny(capabilities)
    : capability.canAll(capabilities);

  if (isAllowed) {
    return true;
  }

  return router.createUrlTree(['/app/no-access']);
};

function collectCapabilities(route: ActivatedRouteSnapshot): Capability[] {
  return route.pathFromRoot.flatMap((snapshot) => {
    const capabilities = snapshot.data['capabilities'];

    return Array.isArray(capabilities) ? capabilities as Capability[] : [];
  });
}

function collectCapabilityMode(route: ActivatedRouteSnapshot): RouteCapabilityMode {
  for (const snapshot of [...route.pathFromRoot].reverse()) {
    const mode = snapshot.data['capabilityMode'];

    if (mode === 'any' || mode === 'all') {
      return mode;
    }
  }

  return 'all';
}
