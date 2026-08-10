import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { finalize } from 'rxjs';

import { GlobalLoadingService } from './global-loading.service';

/**
 * Starts the global loader while HTTP requests are pending.
 */
export const loadingInterceptor: HttpInterceptorFn = (request, next) => {
  const globalLoading = inject(GlobalLoadingService);

  globalLoading.start();

  return next(request).pipe(
    finalize(() => {
      globalLoading.stop();
    }),
  );
};
