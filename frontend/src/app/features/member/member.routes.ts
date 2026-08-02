import { Routes } from '@angular/router';

import { capabilityGuard } from '../../core/guards/capability.guard';
import { MemberDashboardPageComponent } from './pages/member-dashboard-page/member-dashboard-page.component';
import { MemberEventDetailPageComponent } from './pages/member-event-detail-page/member-event-detail-page.component';
import { MemberEventListPageComponent } from './pages/member-event-list-page/member-event-list-page.component';
import { MyProfilePageComponent } from './pages/my-profile-page/my-profile-page.component';
import { MyRegistrationsPageComponent } from './pages/my-registrations-page/my-registrations-page.component';

/**
 * Defines the routes for the user's own member area.
 *
 * This feature represents "me as a club member", not the management of
 * club members.
 */
export const memberRoutes: Routes = [
  {
    path: '',
    canActivateChild: [capabilityGuard],
    data: {
      capabilities: ['canAccessMemberArea'],
    },
    children: [
      {
        path: '',
        component: MemberDashboardPageComponent,
      },
      {
        path: 'events',
        component: MemberEventListPageComponent,
      },
      {
        path: 'events/:eventId',
        component: MemberEventDetailPageComponent,
      },
      {
        path: 'registrations',
        component: MyRegistrationsPageComponent,
      },
      {
        path: 'profile',
        component: MyProfilePageComponent,
      },
    ],
  },
];
