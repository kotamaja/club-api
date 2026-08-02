import { Routes } from '@angular/router';

import { capabilityGuard } from '../../core/guards/capability.guard';
import { ManagementDashboardPageComponent } from './pages/management-dashboard-page/management-dashboard-page.component';

/**
 * Defines the routes for the management area.
 *
 * This feature orchestrates management pages. The member and event business
 * routes are lazy-loaded from their own features.
 */
export const managementRoutes: Routes = [
  {
    path: '',
    canActivateChild: [capabilityGuard],
    data: {
      capabilities: ['canAccessManagementArea'],
    },
    children: [
      {
        path: '',
        component: ManagementDashboardPageComponent,
      },
      {
        path: 'members',
        loadChildren: () => import('../members/members.routes').then((m) => m.membersRoutes),
      },
      {
        path: 'events',
        loadChildren: () => import('../events/events.routes').then((m) => m.eventsRoutes),
      },
    ],
  },
];
