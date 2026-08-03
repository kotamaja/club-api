import { inject, Service } from '@angular/core';

import { AppConfigService } from '../config/app-config.service';

/**
 * Builds versioned API URLs.
 *
 * Feature services should use this service instead of hardcoding /api/v1.
 */
@Service()
export class ApiUrlService {
  private readonly config = inject(AppConfigService);

  build(path: string): string {
    const baseUrl = this.trimEnd(this.config.apiBaseUrl, '/');
    const version = this.trimBoth(this.config.apiVersion, '/');
    const normalizedPath = this.trimStart(path, '/');

    return `${baseUrl}/${version}/${normalizedPath}`;
  }

  private trimStart(value: string, character: string): string {
    while (value.startsWith(character)) {
      value = value.slice(1);
    }

    return value;
  }

  private trimEnd(value: string, character: string): string {
    while (value.endsWith(character)) {
      value = value.slice(0, -1);
    }

    return value;
  }

  private trimBoth(value: string, character: string): string {
    return this.trimStart(this.trimEnd(value, character), character);
  }
}
