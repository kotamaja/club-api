import { DatePipe } from '@angular/common';
import { Component, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { defaultEventListQuery, EventListQuery } from '../../data/event-list-query.model';
import { EventListResource } from '../../data/event-list.resource';
import { EventListItem, EventListPage } from '../../data/event.schema';
import {ErrorStateComponent} from '../../../../shared/ui/error-state/error-state.component';

/**
 * State used by the event management list page.
 */
type EventListPageState =
  | { status: 'loading' }
  | { status: 'reloading'; page: EventListPage }
  | { status: 'empty'; page: EventListPage }
  | { status: 'loaded'; page: EventListPage }
  | { status: 'error'; message: string };

/**
 * Displays the event management list.
 *
 * This page loads events with a reactive HTTP resource and keeps the visible
 * page state as a single discriminated union.
 */
@Component({
  selector: 'app-event-list-page',
  imports: [
    DatePipe,
    RouterLink,
    ErrorStateComponent,
    PageHeaderComponent,
  ],
  templateUrl: './event-list-page.component.html',
  styleUrl: './event-list-page.component.scss',
})
export class EventListPageComponent {
  private readonly eventListResource = inject(EventListResource);

  protected readonly query = signal<EventListQuery>(defaultEventListQuery);

  protected readonly eventsResource = this.eventListResource.create(this.query);

  protected readonly state = computed<EventListPageState>(() => {
    const snapshot = this.eventsResource.snapshot();

    if (snapshot.status === 'error') {

      return {
        status: 'error',
        message: 'Impossible de charger les événements.',
      };
    }

    if (snapshot.status === 'loading') {
      return { status: 'loading' };
    }

    const page = snapshot.value;

    if (snapshot.status === 'reloading') {
      return {
        status: 'reloading',
        page,
      };
    }

    if (page.items.length === 0) {
      return {
        status: 'empty',
        page,
      };
    }

    return {
      status: 'loaded',
      page,
    };
  });

  protected reload(): void {
    this.eventsResource.reload();
  }

  protected nextPage(page: EventListPage): void {
    if (page.page * page.perPage >= page.totalItems) {
      return;
    }

    this.query.update((query) => ({
      ...query,
      page: query.page + 1,
    }));
  }

  protected previousPage(): void {
    this.query.update((query) => ({
      ...query,
      page: Math.max(1, query.page - 1),
    }));
  }

  protected trackEvent(_index: number, event: EventListItem): string {
    return event.id;
  }
}
