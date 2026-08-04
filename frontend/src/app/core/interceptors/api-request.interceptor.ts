import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';


import { TokenService } from '../auth/session/token.service';
import { AppConfigService } from '../config/app-config.service';
import {isApiRequest} from '../api/api-request-url.utils';

/**
 * Prepares outgoing Ramalo API requests.
 *
 * It asks the backend for plain JSON responses and attaches the JWT access
 * token when one is available.
 */
export const apiRequestInterceptor: HttpInterceptorFn = (request, next) => {
  const config = inject(AppConfigService);

  if (!isApiRequest(request.url, config.apiBaseUrl)) {
    return next(request);
  }

  const token = inject(TokenService).getAccessToken();

  return next(
    request.clone({
      setHeaders: {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    }),
  );
};
