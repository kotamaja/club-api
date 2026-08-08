import { DatePipe, Location } from '@angular/common';
import { Component, computed, inject , LOCALE_ID} from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { ErrorStateComponent } from '../../../../shared/ui/error-state/error-state.component';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { eventStatusLabels, eventTypeLabels } from '../../data/options/event-labels';
import { EventDetailResource } from '../../data/detail/event-detail.resource';
import { EventDetail } from '../../data/detail/event-detail.schema';

/**
 * State used by the event detail page.
 */
type EventDetailPageState =
  | { status: 'loading' }
  | { status: 'reloading'; event: EventDetail }
  | { status: 'loaded'; event: EventDetail }
  | { status: 'error'; message: string };

/**
 * Displays one event in read-only mode.
 */
@Component({
  selector: 'app-event-detail-page',
  imports: [
    DatePipe,
    RouterLink,
    ErrorStateComponent,
    PageHeaderComponent,
  ],
  templateUrl: './event-detail-page.component.html',
  styleUrl: './event-detail-page.component.scss',
})
export class EventDetailPageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly eventDetailResource = inject(EventDetailResource);
  private readonly location = inject(Location);
  private readonly locale = inject(LOCALE_ID);

  protected readonly eventId = computed(() => this.route.snapshot.paramMap.get('eventId'));

  protected readonly eventResource = this.eventDetailResource.create(this.eventId);

  protected readonly state = computed<EventDetailPageState>(() => {

    if (this.eventId() === null) {
      return {
        status: 'error',
        message: 'Identifiant d’événement manquant.',
      };
    }

    const snapshot = this.eventResource.snapshot();

    if (snapshot.status === 'error') {
      return {
        status: 'error',
        message: 'Impossible de charger le détail de l’événement.',
      };
    }

    if (snapshot.status === 'loading' || snapshot.value === undefined) {
      return { status: 'loading' };
    }

    if (snapshot.status === 'reloading') {
      return {
        status: 'reloading',
        event: snapshot.value,
      };
    }

    return {
      status: 'loaded',
      event: snapshot.value,
    };
  });

  protected reload(): void {
    this.eventResource.reload();
  }

  /**
   * Returns the human-readable label for an event type.
   */
  protected eventTypeLabel(eventType: EventDetail['eventType']): string {
    return eventTypeLabels[eventType] ?? eventType;
  }

  /**
   * Returns the human-readable label for an event status.
   */
  protected eventStatusLabel(eventStatus: EventDetail['eventStatus']): string {
    return eventStatusLabels[eventStatus] ?? eventStatus;
  }

  /**
   * Returns the visible registration/capacity summary for the event.
   */
  protected registrationSummary(event: EventDetail): string {
    if (event.capacity === null) {
      return `${event.registeredCount} / illimité`;
    }

    return `${event.registeredCount} / ${event.capacity}`;
  }

  /**
   * Returns a readable yes/no label.
   */
  protected yesNo(value: boolean): string {
    return value ? 'Oui' : 'Non';
  }

  /**
   * Returns to the previous page while preserving its URL state.
   */
  protected goBack(): void {
    this.location.back();
  }


  /**
   * Returns the formatted event date range.
   */
  protected dateRangeLabel(event: EventDetail): string {
    const startsAt = new Date(event.startsAt);
    const endsAt = new Date(event.endsAt);
    const sameEventDay = this.eventDateKey(event.startsAt, event.timezone) === this.eventDateKey(event.endsAt, event.timezone);

    if (sameEventDay) {
      return startsAt.toLocaleDateString(this.locale, {
        weekday: 'long',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: event.timezone,
      });
    }

    return `${startsAt.toLocaleDateString(this.locale, {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      timeZone: event.timezone,
    })} – ${endsAt.toLocaleDateString(this.locale, {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      timeZone: event.timezone,
    })}`;
  }

  /**
   * Returns the formatted event time range.
   */
  protected timeRangeLabel(event: EventDetail): string {
    if (event.allDay) {
      return 'Journée entière';
    }

    const startsAt = new Date(event.startsAt);
    const endsAt = new Date(event.endsAt);

    return `${startsAt.toLocaleTimeString(this.locale, {
      hour: '2-digit',
      minute: '2-digit',
      timeZone: event.timezone,
    })} – ${endsAt.toLocaleTimeString(this.locale, {
      hour: '2-digit',
      minute: '2-digit',
      timeZone: event.timezone,
    })}`;
  }

  /**
   * Returns a comparable date key in the event timezone.
   */
  private eventDateKey(value: string, timezone: string): string {
    return new Intl.DateTimeFormat(this.locale, {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      timeZone: timezone,
    }).format(new Date(value));
  }

}
