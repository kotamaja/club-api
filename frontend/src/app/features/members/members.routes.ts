import { Routes } from '@angular/router';

import { capabilityGuard } from '../../core/guards/capability.guard';
import { MemberCreatePageComponent } from './pages/member-create-page/member-create-page.component';
import { MemberDetailPageComponent } from './pages/member-detail-page/member-detail-page.component';
import { MemberEditPageComponent } from './pages/member-edit-page/member-edit-page.component';
import { MemberListPageComponent } from './pages/member-list-page/member-list-page.component';

/**
 * Defines the routes for managing club members.
 *
 * This feature represents "members managed by a club operator or manager".
 */
export const membersRoutes: Routes = [
  {
    path: '',
    canActivateChild: [capabilityGuard],
    data: {
      capabilities: ['canManageMembers'],
    },
    children: [
      {
        path: '',
        component: MemberListPageComponent,
      },
      {
        path: 'new',
        component: MemberCreatePageComponent,
      },
      {
        path: ':personId',
        component: MemberDetailPageComponent,
      },
      {
        path: ':personId/edit',
        component: MemberEditPageComponent,
      },
    ],
  },
];
