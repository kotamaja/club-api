import { Routes } from '@angular/router';

import { contextGuard } from '../../core/guards/context.guard';
import { ConnectedShellComponent } from './components/connected-shell/connected-shell.component';
import { AppEntryPageComponent } from './pages/app-entry-page/app-entry-page.component';
import { AppHomePageComponent } from './pages/app-home-page/app-home-page.component';
import { NoAccessPageComponent } from './pages/no-access-page/no-access-page.component';
import { SelectClubPageComponent } from './pages/select-club-page/select-club-page.component';
import { SelectOrganizationPageComponent } from './pages/select-organization-page/select-organization-page.component';

/**
 * Defines the connected application shell routes.
 *
 * All routes under /app share the connected layout with header, navigation
 * and the main router outlet.
 */
export const shellRoutes: Routes = [
  {
    path: '',
    component: ConnectedShellComponent,
    children: [
      {
        path: '',
        component: AppEntryPageComponent,
      },
      {
        path: 'home',
        canActivate: [contextGuard],
        component: AppHomePageComponent,
      },
      {
        path: 'select-organization',
        component: SelectOrganizationPageComponent,
      },
      {
        path: 'select-club',
        component: SelectClubPageComponent,
      },
      {
        path: 'no-access',
        component: NoAccessPageComponent,
      },
      {
        path: 'member',
        canActivate: [contextGuard],
        loadChildren: () => import('../member/member.routes').then((m) => m.memberRoutes),
      },
      {
        path: 'manage',
        canActivate: [contextGuard],
        loadChildren: () => import('../management/management.routes').then((m) => m.managementRoutes),
      },
    ],
  },
];
