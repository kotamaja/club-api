import { Component } from '@angular/core';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * Displays the event management list.
 *
 * This page will later provide event filters, sorting, pagination and access
 * to event creation and detail pages.
 */
@Component({
  selector: 'app-event-list-page',
  imports: [PageHeaderComponent],
  templateUrl: './event-list-page.component.html',
  styleUrl: './event-list-page.component.scss',
})
export class EventListPageComponent {
}
