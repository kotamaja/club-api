import {Routes} from '@angular/router';

import {capabilityGuard} from '../../core/guards/capability.guard';
import {EventCreatePageComponent} from './pages/event-create-page/event-create-page.component';
import {EventDetailPageComponent} from './pages/event-detail-page/event-detail-page.component';
import {EventEditPageComponent} from './pages/event-edit-page/event-edit-page.component';
import {EventListPageComponent} from './pages/event-list-page/event-list-page.component';

/**
 * Defines the routes for managing club events.
 *
 * Event registrations and public registration requests are attached to an
 * event and can later use more specific capabilities.
 */
export const eventsRoutes: Routes = [
  {
    path: '',
    canActivateChild: [capabilityGuard],
    data: {
      capabilities: ['canManageEvents'],
    },
    children: [
      {
        path: '',
        component: EventListPageComponent,
      },
      {
        path: ':eventId/registrations',
        data: {
          capabilities: ['canManageEventRegistrations'],
        },
        loadComponent: () =>
          import('./pages/event-registrations-page/event-registrations-page.component')
            .then((m) => m.EventRegistrationsPageComponent),
      },
      {
        path: ':eventId/public-registration-requests',
        data: {
          capabilities: ['canReviewPublicRegistrationRequests'],
        },
        loadComponent: () =>
          import('./components/public-registration-requests-panel/public-registration-requests-panel.component')
            .then((m) => m.PublicRegistrationRequestsPanelComponent),
      },
      {
        path: 'new',
        component: EventCreatePageComponent,
      },
      {
        path: ':eventId',
        component: EventDetailPageComponent,
      },
      {
        path: ':eventId/edit',
        component: EventEditPageComponent,
      },
    ],
  },
];
