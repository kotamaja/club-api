import { inject, Service } from '@angular/core';

import { AppConfig } from './app-config.model';
import { APP_CONFIG } from './app-config.token';

/**
 * Exposes the current application configuration.
 *
 * Services should use this class instead of importing configuration values
 * directly.
 */
@Service()
export class AppConfigService {
  private readonly config = inject(APP_CONFIG);

  get apiBaseUrl(): string {
    return this.config.apiBaseUrl;
  }

  get apiVersion(): AppConfig['apiVersion'] {
    return this.config.apiVersion;
  }

  get snapshot(): AppConfig {
    return this.config;
  }
}
