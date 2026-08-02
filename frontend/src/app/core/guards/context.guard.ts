import { CanActivateFn } from '@angular/router';

export const contextGuard: CanActivateFn = (route, state) => {
  return true;
};
