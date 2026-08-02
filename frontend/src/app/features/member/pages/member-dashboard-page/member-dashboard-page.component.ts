import { Component } from '@angular/core';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * Displays the member dashboard entry page.
 *
 * This page will later show upcoming events, the user's next registrations,
 * club information and quick member actions.
 */
@Component({
  selector: 'app-member-dashboard-page',
  imports: [PageHeaderComponent],
  templateUrl: './member-dashboard-page.component.html',
  styleUrl: './member-dashboard-page.component.scss',
})
export class MemberDashboardPageComponent {
}
