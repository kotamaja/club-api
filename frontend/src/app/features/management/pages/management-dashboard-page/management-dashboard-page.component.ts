import { Component } from '@angular/core';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * Displays the management dashboard entry page.
 *
 * This page will later show member follow-up cards, upcoming events,
 * pending public requests and quick management actions.
 */
@Component({
  selector: 'app-management-dashboard-page',
  imports: [PageHeaderComponent],
  templateUrl: './management-dashboard-page.component.html',
  styleUrl: './management-dashboard-page.component.scss',
})
export class ManagementDashboardPageComponent {
}
