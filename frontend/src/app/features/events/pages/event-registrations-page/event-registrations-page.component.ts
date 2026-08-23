import { DatePipe, Location } from '@angular/common';
import { Component, computed, inject, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { ErrorStateComponent } from '../../../../shared/ui/error-state/error-state.component';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { EventDetailResource } from '../../data/detail/event-detail.resource';
import {
  defaultEventRegistrationListQuery,
  EventRegistrationListQuery,
} from '../../data/list/event-registration-list-query.model';
import {
  EventRegistrationListItem,
  EventRegistrationListPage,
} from '../../data/list/event-registration-list.schema';
import { EventRegistrationListResource } from '../../data/list/event-registration-list.resource';
import { eventRegistrationStatusLabels } from '../../data/options/event-registration-labels';
import { EventRegistrationStatus } from '../../data/shared/event-registration.schema';
import { EventRegistrationWriteService } from '../../data/write/event-registration-write.service';

type EventRegistrationsPageState =
  | { status: 'loading' }
  | { status: 'reloading'; page: EventRegistrationListPage }
  | { status: 'empty'; page: EventRegistrationListPage }
  | { status: 'loaded'; page: EventRegistrationListPage }
  | { status: 'error'; message: string };

type EventRegistrationCancelState =
  | { status: 'idle' }
  | { status: 'cancelling'; registrationId: string }
  | { status: 'error'; message: string };

/**
 * Displays the management registration list for one event.
 *
 * Filters and sort are synchronized with the URL.
 */
@Component({
  selector: 'app-event-registrations-page',
  imports: [
    DatePipe,
    ErrorStateComponent,
    PageHeaderComponent,
    RouterLink,
  ],
  templateUrl: './event-registrations-page.component.html',
  styleUrl: './event-registrations-page.component.scss',
})
export class EventRegistrationsPageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly eventDetailResource = inject(EventDetailResource);
  private readonly registrationListResource = inject(EventRegistrationListResource);
  private readonly registrationWrite = inject(EventRegistrationWriteService);
  private readonly location = inject(Location);


  protected readonly eventId = computed(() => this.route.snapshot.paramMap.get('eventId') ?? '');
  protected readonly query = signal<EventRegistrationListQuery>(this.createInitialQuery());
  protected readonly currentQuery = computed(() => this.query());
  protected readonly cancelState = signal<EventRegistrationCancelState>({ status: 'idle' });

  protected readonly eventResource = this.eventDetailResource.create(this.eventId);
  protected readonly registrationsResource = this.registrationListResource.create(this.eventId, this.query);

  protected readonly state = computed<EventRegistrationsPageState>(() => {
    const snapshot = this.registrationsResource.snapshot();

    if (snapshot.status === 'error') {
      return {
        status: 'error',
        message: 'Impossible de charger les inscriptions de cet événement.',
      };
    }

    if (snapshot.status === 'loading' || snapshot.value === undefined) {
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

  protected readonly statusOptions: EventRegistrationStatus[] = [
    'registered',
    'waitlisted',
    'cancelled',
  ];

  protected reload(): void {
    this.eventResource.reload();
    this.registrationsResource.reload();
  }

  protected updateStatus(status: EventRegistrationListQuery['status']): void {
    this.updateQuery({
      ...this.query(),
      status,
      page: 1,
    });
  }

  protected updateSort(sort: EventRegistrationListQuery['sort']): void {
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

  protected sortLabel(sort: EventRegistrationListQuery['sort']): string {
    const query = this.query();

    if (query.sort !== sort) {
      return '';
    }

    return query.direction === 'asc' ? '↑' : '↓';
  }

  protected previousPage(): void {
    this.updateQuery({
      ...this.query(),
      page: Math.max(1, this.query().page - 1),
    });
  }

  protected nextPage(page: EventRegistrationListPage): void {
    if (page.page * page.perPage >= page.totalItems) {
      return;
    }

    this.updateQuery({
      ...this.query(),
      page: this.query().page + 1,
    });
  }

  protected totalPages(page: EventRegistrationListPage): number {
    return Math.max(1, Math.ceil(page.totalItems / page.perPage));
  }

  protected resetFilters(): void {
    this.updateQuery(defaultEventRegistrationListQuery);
  }

  protected hasActiveFilters(): boolean {
    const query = this.query();

    return query.status !== defaultEventRegistrationListQuery.status
      || query.sort !== defaultEventRegistrationListQuery.sort
      || query.direction !== defaultEventRegistrationListQuery.direction
      || query.page !== defaultEventRegistrationListQuery.page
      || query.perPage !== defaultEventRegistrationListQuery.perPage;
  }

  protected statusLabel(status: EventRegistrationStatus): string {
    return eventRegistrationStatusLabels[status] ?? status;
  }

  protected personName(registration: EventRegistrationListItem): string {
    return `${registration.personFirstname} ${registration.personLastname}`.trim();
  }

  protected canCancel(registration: EventRegistrationListItem): boolean {
    return registration.status !== 'cancelled';
  }

  protected isCancellingRegistration(registration: EventRegistrationListItem): boolean {
    const state = this.cancelState();

    return state.status === 'cancelling' && state.registrationId === registration.id;
  }

  protected cancelRegistration(registration: EventRegistrationListItem): void {
    if (!this.canCancel(registration)) {
      return;
    }

    const confirmed = window.confirm(`Annuler l'inscription de ${this.personName(registration)} ?`);

    if (!confirmed) {
      return;
    }

    this.cancelState.set({
      status: 'cancelling',
      registrationId: registration.id,
    });

    this.registrationWrite.cancel(registration.id).subscribe({
      next: () => {
        this.cancelState.set({ status: 'idle' });
        this.reload();
      },
      error: () => {
        this.cancelState.set({
          status: 'error',
          message: 'Impossible d’annuler cette inscription.',
        });
      },
    });
  }

  protected trackRegistration(_index: number, registration: EventRegistrationListItem): string {
    return registration.id;
  }

  private createInitialQuery(): EventRegistrationListQuery {
    const queryParamMap = this.route.snapshot.queryParamMap;

    return {
      status: this.toStatus(queryParamMap.get('status')),
      sort: this.toSort(queryParamMap.get('sort')),
      direction: queryParamMap.get('direction') === 'asc' ? 'asc' : 'desc',
      page: this.toPositiveInteger(queryParamMap.get('page'), defaultEventRegistrationListQuery.page),
      perPage: this.toPositiveInteger(queryParamMap.get('perPage'), defaultEventRegistrationListQuery.perPage),
    };
  }

  private updateQuery(nextQuery: EventRegistrationListQuery): void {
    this.query.set(nextQuery);

    void this.router.navigate([], {
      relativeTo: this.route,
      queryParams: {
        status: nextQuery.status,
        sort: nextQuery.sort,
        direction: nextQuery.direction,
        page: nextQuery.page,
        perPage: nextQuery.perPage,
      },
      queryParamsHandling: 'merge',
      replaceUrl: true,
    });
  }

  private toStatus(value: string | null): EventRegistrationListQuery['status'] {
    if (value === 'registered' || value === 'waitlisted' || value === 'cancelled') {
      return value;
    }

    return 'all';
  }

  private toSort(value: string | null): EventRegistrationListQuery['sort'] {
    if (value === 'personLastname' || value === 'status' || value === 'cancelledAt') {
      return value;
    }

    return 'requestedAt';
  }

  private toPositiveInteger(value: string | null, fallback: number): number {
    if (value === null) {
      return fallback;
    }

    const parsedValue = Number.parseInt(value, 10);

    return Number.isFinite(parsedValue) && parsedValue > 0 ? parsedValue : fallback;
  }


  protected backToEvent(): void {
    this.location.back();
  }
}
