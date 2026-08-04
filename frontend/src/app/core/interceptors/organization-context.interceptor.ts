import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';

import { extractRequestPath, isApiRequest } from '../api/api-request-url.utils';
import { AppConfigService } from '../config/app-config.service';
import { CurrentContextService } from '../context/current/current-context.service';

/**
 * Adds the current organization id to Ramalo business API requests.
 *
 * Authentication and current-user endpoints do not require this header.
 */
export const organizationContextInterceptor: HttpInterceptorFn = (request, next) => {
  const config = inject(AppConfigService);

  if (!isApiRequest(request.url, config.apiBaseUrl)) {
    return next(request);
  }

  if (shouldSkipOrganizationContext(request.url)) {
    return next(request);
  }

  const currentContext = inject(CurrentContextService);
  const organization = currentContext.currentOrganization();

  if (organization === null) {
    return next(request);
  }

  return next(
    request.clone({
      setHeaders: {
        'X-Organization-Id': organization.id,
      },
    }),
  );
};

function shouldSkipOrganizationContext(url: string): boolean {
  const path = extractRequestPath(url);

  return path.endsWith('/auth/login')
    || path.endsWith('/auth/refresh')
    || path.endsWith('/auth/logout')
    || path.endsWith('/me')
    || path.endsWith('/me/organizations');
}
