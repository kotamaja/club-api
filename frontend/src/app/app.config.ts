import { ApplicationConfig, provideBrowserGlobalErrorListeners, isDevMode } from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { provideServiceWorker } from '@angular/service-worker';
import {APP_CONFIG} from './core/config/app-config.token';
import {provideHttpClient, withInterceptors} from '@angular/common/http';
import {apiRequestInterceptor} from './core/interceptors/api-request.interceptor';


export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    provideServiceWorker('ngsw-worker.js', {
      enabled: !isDevMode(),
      registrationStrategy: 'registerWhenStable:30000',
    }),
    provideServiceWorker('ngsw-worker.js', {
      enabled: !isDevMode(),
      registrationStrategy: 'registerWhenStable:30000',
    }),
    provideHttpClient(
      withInterceptors([
        apiRequestInterceptor,
      ]),
    ),
    {
      provide: APP_CONFIG,
      useValue: {
        apiBaseUrl: '/api',
        apiVersion: 'v1',
      },
    },
  ],
};
