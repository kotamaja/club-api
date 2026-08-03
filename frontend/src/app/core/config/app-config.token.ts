import { InjectionToken } from '@angular/core';

import { AppConfig } from './app-config.model';

/**
 * Injection token providing the application runtime configuration.
 */
export const APP_CONFIG = new InjectionToken<AppConfig>('APP_CONFIG');
