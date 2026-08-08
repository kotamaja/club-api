import {DatePipe} from '@angular/common';
import {Component, computed, inject, signal} from '@angular/core';
import {ActivatedRoute, Router, RouterLink} from '@angular/router';

import {ErrorStateComponent} from '../../../../shared/ui/error-state/error-state.component';
import {PageHeaderComponent} from '../../../../shared/ui/page-header/page-header.component';
import {defaultEventListQuery, EventListQuery} from '../../data/list/event-list-query.model';
import {
  eventListQueryFromUrl,
  eventListQueryToUrl,
  eventListSelectedIdFromUrl,
  eventListSelectedIdToUrl,
} from '../../data/list/event-list-query-url.mapper';
import {EventListResource} from '../../data/list/event-list.resource';
import { eventStatusLabels, eventTypeLabels } from '../../data/options/event-labels';
import {EventListItem, EventListPage} from '../../data/list/event-list.schema';


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
 * Filters are synchronized with the URL. The selected row is kept separately
 * because it is a UI state and does not affect the API query.
 */
@Component({
  selector: 'app-event-list-page',
  imports: [
    DatePipe,
    ErrorStateComponent,
    PageHeaderComponent,
    RouterLink,
  ],
  templateUrl: './event-list-page.component.html',
  styleUrl: './event-list-page.component.scss',
})
export class EventListPageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly eventListResource = inject(EventListResource);

  private searchDebounceId: ReturnType<typeof setTimeout> | null = null;

  protected readonly query = signal<EventListQuery>(this.createInitialQuery());
  protected readonly selectedEventId = signal<string | null>(this.createInitialSelectedEventId());

  protected readonly currentQuery = computed(() => this.query());

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
      return {status: 'loading'};
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

  protected goToCreateEvent(): void {
    void this.router.navigate(['/app/manage/events/new']);
  }

  protected updateSearch(search: string): void {
    if (this.searchDebounceId !== null) {
      clearTimeout(this.searchDebounceId);
    }

    this.searchDebounceId = setTimeout(() => {
      const currentQuery = this.query();

      if (currentQuery.search === search) {
        this.searchDebounceId = null;
        return;
      }

      this.updateQuery({
        ...currentQuery,
        search,
        page: 1,
      });

      this.searchDebounceId = null;
    }, 300);
  }

  protected updatePeriod(period: EventListQuery['period']): void {
    this.updateQuery({
      ...this.query(),
      period,
      page: 1,
    });
  }

  protected updateStatus(eventStatus: EventListQuery['eventStatus']): void {
    this.updateQuery({
      ...this.query(),
      eventStatus,
      page: 1,
    });
  }

  protected updateType(eventType: EventListQuery['eventType']): void {
    this.updateQuery({
      ...this.query(),
      eventType,
      page: 1,
    });
  }

  protected updateSort(sort: EventListQuery['sort']): void {
    const query = this.query();

    if (query.sort === sort) {
      this.updateQuery({
        ...query,
        direction: query.direction === 'asc' ? 'desc' : 'asc',
        page: 1,
      });

      return;
    }

    this.updateQuery({
      ...query,
      sort,
      direction: 'asc',
      page: 1,
    });
  }

  protected nextPage(page: EventListPage): void {
    if (page.page * page.perPage >= page.totalItems) {
      return;
    }

    this.updateQuery({
      ...this.query(),
      page: this.query().page + 1,
    });
  }

  protected previousPage(): void {
    this.updateQuery({
      ...this.query(),
      page: Math.max(1, this.query().page - 1),
    });
  }

  protected sortLabel(sort: EventListQuery['sort']): string {
    const query = this.query();

    if (query.sort !== sort) {
      return '';
    }

    return query.direction === 'asc' ? '↑' : '↓';
  }

  protected trackEvent(_index: number, event: EventListItem): string {
    return event.id;
  }

  private createInitialQuery(): EventListQuery {
    const queryParamMap = this.route.snapshot.queryParamMap;

    return eventListQueryFromUrl({
      search: queryParamMap.get('search'),
      period: queryParamMap.get('period'),
      eventStatus: queryParamMap.get('eventStatus'),
      eventType: queryParamMap.get('eventType'),
      sort: queryParamMap.get('sort'),
      direction: queryParamMap.get('direction'),
      page: queryParamMap.get('page'),
      perPage: queryParamMap.get('perPage'),
    });
  }

  private createInitialSelectedEventId(): string | null {
    return eventListSelectedIdFromUrl({
      selected: this.route.snapshot.queryParamMap.get('selected'),
    });
  }

  private updateQuery(nextQuery: EventListQuery): void {
    this.query.set(nextQuery);

    void this.router.navigate([], {
      relativeTo: this.route,
      queryParams: eventListQueryToUrl(nextQuery),
      queryParamsHandling: 'merge',
      replaceUrl: true,
    });
  }

  /**
   * Selects an event in the current list URL, then opens its detail page.
   */
  protected openEvent(event: EventListItem): void {
    this.selectedEventId.set(event.id);

    void this.router.navigate([], {
      relativeTo: this.route,
      queryParams: eventListSelectedIdToUrl(event.id),
      queryParamsHandling: 'merge',
      replaceUrl: true,
    }).then(() => {
      void this.router.navigate(['/app/manage/events', event.id]);
    });
  }

  /**
   * Returns the total number of pages for a paginated event list.
   */
  protected totalPages(page: EventListPage): number {
    return Math.max(1, Math.ceil(page.totalItems / page.perPage));
  }

  /**
   * Resets the event list filters, sort and pagination.
   */
  protected resetFilters(): void {
    this.updateQuery(defaultEventListQuery);
  }

  /**
   * Returns the human-readable label for an event type.
   */
  protected eventTypeLabel(eventType: EventListItem['eventType']): string {
    return eventTypeLabels[eventType] ?? eventType;
  }

  /**
   * Returns the human-readable label for an event status.
   */
  protected eventStatusLabel(eventStatus: EventListItem['eventStatus']): string {
    return eventStatusLabels[eventStatus] ?? eventStatus;
  }

  /**
   * Returns the visible registration/capacity summary for one event.
   */
  protected registrationSummary(event: EventListItem): string {
    if (event.capacity === null) {
      return `${event.registeredCount} / illimité`;
    }

    return `${event.registeredCount} / ${event.capacity}`;
  }

  /**
   * Returns true when at least one filter or sort value differs from defaults.
   */
  protected hasActiveFilters(): boolean {
    const query = this.query();

    return query.search.trim() !== defaultEventListQuery.search
      || query.period !== defaultEventListQuery.period
      || query.eventStatus !== defaultEventListQuery.eventStatus
      || query.eventType !== defaultEventListQuery.eventType
      || query.sort !== defaultEventListQuery.sort
      || query.direction !== defaultEventListQuery.direction
      || query.page !== defaultEventListQuery.page
      || query.perPage !== defaultEventListQuery.perPage;
  }

}

