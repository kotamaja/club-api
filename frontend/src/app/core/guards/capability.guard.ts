import { CanActivateFn } from '@angular/router';

export const capabilityGuard: CanActivateFn = (route, state) => {
  return true;
};
