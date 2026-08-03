import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';

import { TokenService } from '../auth/token.service';
import { AppConfigService } from '../config/app-config.service';

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

function isApiRequest(url: string, apiBaseUrl: string): boolean {
  const normalizedApiBaseUrl = normalizePath(apiBaseUrl);

  if (url.startsWith('http://') || url.startsWith('https://')) {
    try {
      const parsedUrl = new URL(url);

      return normalizePath(parsedUrl.pathname).startsWith(normalizedApiBaseUrl);
    } catch {
      return false;
    }
  }

  return normalizePath(url).startsWith(normalizedApiBaseUrl);
}

function normalizePath(path: string): string {
  const trimmed = path.trim();

  if (trimmed === '') {
    return '/';
  }

  const withLeadingSlash = trimmed.startsWith('/') ? trimmed : `/${trimmed}`;

  return withLeadingSlash.endsWith('/') && withLeadingSlash !== '/'
    ? withLeadingSlash.slice(0, -1)
    : withLeadingSlash;
}
