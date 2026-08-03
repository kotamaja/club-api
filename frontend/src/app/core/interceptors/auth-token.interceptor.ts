import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';

import { AppConfigService } from '../config/app-config.service';
import { TokenService } from '../auth/token.service';

/**
 * Adds the current JWT access token to Ramalo API requests.
 *
 * Refresh-token handling is intentionally not implemented here yet. This
 * interceptor only attaches the bearer token when one is available.
 */
export const authTokenInterceptor: HttpInterceptorFn = (request, next) => {
  const config = inject(AppConfigService);
  const token = inject(TokenService).getAccessToken();

  if (!token || !isApiRequest(request.url, config.apiBaseUrl)) {
    return next(request);
  }

  return next(
    request.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`,
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
