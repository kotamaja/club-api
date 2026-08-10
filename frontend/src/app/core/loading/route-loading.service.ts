import { DestroyRef, inject, Service } from '@angular/core';
import {
  NavigationCancel,
  NavigationEnd,
  NavigationError,
  NavigationStart,
  Router,
} from '@angular/router';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';

import { GlobalLoadingService } from './global-loading.service';

/**
 * Tracks Angular route navigation activity for the global loader.
 */
@Service()
export class RouteLoadingService {
  private readonly router = inject(Router);
  private readonly destroyRef = inject(DestroyRef);
  private readonly globalLoading = inject(GlobalLoadingService);

  start(): void {
    this.router.events
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe((event) => {
        if (event instanceof NavigationStart) {
          this.globalLoading.start();
          return;
        }

        if (
          event instanceof NavigationEnd
          || event instanceof NavigationCancel
          || event instanceof NavigationError
        ) {
          this.globalLoading.stop();
        }
      });
  }
}
