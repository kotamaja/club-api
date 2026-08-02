import { Component } from '@angular/core';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * Displays the club member management list.
 *
 * This page will later provide search, filters, sorting, pagination and access
 * to member creation and detail pages.
 */
@Component({
  selector: 'app-member-list-page',
  imports: [PageHeaderComponent],
  templateUrl: './member-list-page.component.html',
  styleUrl: './member-list-page.component.scss',
})
export class MemberListPageComponent {
}
