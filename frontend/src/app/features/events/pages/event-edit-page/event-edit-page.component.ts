import { Location } from '@angular/common';
import { Component, computed, effect, inject, signal } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';

import { ErrorStateComponent } from '../../../../shared/ui/error-state/error-state.component';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { eventStatusLabels, eventTypeLabels } from '../../data/event-labels';
import { EventDetailResource } from '../../data/event-detail.resource';
import { EventDetail } from '../../data/event-detail.schema';
import { EventUpdatePayload } from '../../data/event-update.model';
import { EventWriteService } from '../../data/event-write.service';

/**
 * Editable form state for an event.
 */
type EventEditForm = {
  title: string;
  description: string;
  eventType: EventDetail['eventType'];
  eventStatus: EventDetail['eventStatus'];
  clubId: string | null;
  location: string;
  startsAt: string;
  endsAt: string;
  timezone: string;
  allDay: boolean;
  capacity: number | null;
  waitlistEnabled: boolean;
  publicRegistrationEnabled: boolean;
  registrationStartsAt: string;
  registrationEndsAt: string;
};

/**
 * Save state used by the event edit page.
 */
type EventEditSaveState =
  | { status: 'idle' }
  | { status: 'saving' }
  | { status: 'saved' }
  | { status: 'error'; message: string };

/**
 * Page state used by the event edit page.
 */
type EventEditPageState =
  | { status: 'loading' }
  | { status: 'ready'; event: EventDetail; form: EventEditForm; save: EventEditSaveState }
  | { status: 'error'; message: string };

/**
 * Displays the event edit form.
 */
@Component({
  selector: 'app-event-edit-page',
  imports: [
    ErrorStateComponent,
    PageHeaderComponent,
  ],
  templateUrl: './event-edit-page.component.html',
  styleUrl: './event-edit-page.component.scss',
})
export class EventEditPageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly location = inject(Location);
  private readonly eventDetailResource = inject(EventDetailResource);
  private readonly eventWriteService = inject(EventWriteService);

  protected readonly eventId = computed(() => this.route.snapshot.paramMap.get('eventId'));
  protected readonly eventResource = this.eventDetailResource.create(this.eventId);

  protected readonly form = signal<EventEditForm | null>(null);
  protected readonly initializedEventId = signal<string | null>(null);
  protected readonly saveState = signal<EventEditSaveState>({ status: 'idle' });

  private readonly initializeFormEffect = effect(() => {
    const snapshot = this.eventResource.snapshot();

    if (snapshot.status === 'error' || snapshot.status === 'loading' || snapshot.value === undefined) {
      return;
    }

    const event = snapshot.value;

    if (this.initializedEventId() === event.id) {
      return;
    }

    this.form.set(this.createForm(event));
    this.initializedEventId.set(event.id);
    this.saveState.set({ status: 'idle' });
  });

  protected readonly state = computed<EventEditPageState>(() => {
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
        message: 'Impossible de charger l’événement à modifier.',
      };
    }

    const form = this.form();

    if (snapshot.status === 'loading' || snapshot.value === undefined || form === null) {
      return { status: 'loading' };
    }

    return {
      status: 'ready',
      event: snapshot.value,
      form,
      save: this.saveState(),
    };
  });

  protected reload(): void {
    this.initializedEventId.set(null);
    this.eventResource.reload();
  }

  protected goBack(): void {
    this.location.back();
  }

  protected updateTitle(title: string): void {
    this.patchForm({ title });
  }

  protected updateDescription(description: string): void {
    this.patchForm({ description });
  }

  protected updateType(eventType: EventDetail['eventType']): void {
    this.patchForm({ eventType });
  }

  protected updateStatus(eventStatus: EventDetail['eventStatus']): void {
    this.patchForm({ eventStatus });
  }

  protected updateLocation(location: string): void {
    this.patchForm({ location });
  }

  protected updateStartsAt(startsAt: string): void {
    this.patchForm({ startsAt });
  }

  protected updateEndsAt(endsAt: string): void {
    this.patchForm({ endsAt });
  }

  protected updateAllDay(allDay: boolean): void {
    this.patchForm({ allDay });
  }

  protected updateCapacity(value: string): void {
    const trimmed = value.trim();

    this.patchForm({
      capacity: trimmed === '' ? null : Number(trimmed),
    });
  }

  protected updateWaitlistEnabled(waitlistEnabled: boolean): void {
    this.patchForm({ waitlistEnabled });
  }

  protected updatePublicRegistrationEnabled(publicRegistrationEnabled: boolean): void {
    this.patchForm({ publicRegistrationEnabled });
  }

  protected updateRegistrationStartsAt(registrationStartsAt: string): void {
    this.patchForm({ registrationStartsAt });
  }

  protected updateRegistrationEndsAt(registrationEndsAt: string): void {
    this.patchForm({ registrationEndsAt });
  }

  protected save(event: EventDetail, form: EventEditForm): void {
    if (this.saveState().status === 'saving') {
      return;
    }

    this.saveState.set({ status: 'saving' });

    this.eventWriteService.update(event.id, this.createPayload(event, form)).subscribe({
      next: (updatedEvent) => {
        this.saveState.set({ status: 'saved' });

        void this.router.navigate(['/app/manage/events', updatedEvent.id], {
          replaceUrl: true,
        });
      },
      error: () => {
        this.saveState.set({
          status: 'error',
          message: 'Impossible d’enregistrer les modifications.',
        });
      },
    });
  }

  protected eventTypeLabel(eventType: EventDetail['eventType']): string {
    return eventTypeLabels[eventType] ?? eventType;
  }

  protected eventStatusLabel(eventStatus: EventDetail['eventStatus']): string {
    return eventStatusLabels[eventStatus] ?? eventStatus;
  }

  private patchForm(patch: Partial<EventEditForm>): void {
    this.form.update((form) => {
      if (form === null) {
        return form;
      }

      return {
        ...form,
        ...patch,
      };
    });

    if (this.saveState().status === 'saved' || this.saveState().status === 'error') {
      this.saveState.set({ status: 'idle' });
    }
  }

  private createForm(event: EventDetail): EventEditForm {
    return {
      title: event.title,
      description: event.description ?? '',
      eventType: event.eventType,
      eventStatus: event.eventStatus,
      clubId: event.clubId,
      location: event.location ?? '',
      startsAt: this.toDateTimeLocalValue(event.startsAt),
      endsAt: this.toDateTimeLocalValue(event.endsAt),
      timezone: event.timezone,
      allDay: event.allDay,
      capacity: event.capacity,
      waitlistEnabled: event.waitlistEnabled,
      publicRegistrationEnabled: event.publicRegistrationEnabled,
      registrationStartsAt: this.toDateTimeLocalValue(event.registrationStartsAt),
      registrationEndsAt: this.toDateTimeLocalValue(event.registrationEndsAt),
    };
  }

  private createPayload(event: EventDetail, form: EventEditForm): EventUpdatePayload {
    return {
      title: form.title.trim(),
      description: this.toNullableString(form.description),
      eventType: form.eventType,
      eventStatus: form.eventStatus,
      clubId: form.clubId,
      location: this.toNullableString(form.location),
      startsAt: this.toApiDateTime(form.startsAt, event.startsAt),
      endsAt: this.toApiDateTime(form.endsAt, event.endsAt),
      timezone: form.timezone,
      allDay: form.allDay,
      capacity: form.capacity,
      waitlistEnabled: form.waitlistEnabled,
      publicRegistrationEnabled: form.publicRegistrationEnabled,
      registrationStartsAt: this.toNullableApiDateTime(form.registrationStartsAt, event.registrationStartsAt ?? event.startsAt),
      registrationEndsAt: this.toNullableApiDateTime(form.registrationEndsAt, event.registrationEndsAt ?? event.startsAt),
    };
  }

  private toNullableString(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
  }

  private toDateTimeLocalValue(value: string | null): string {
    if (value === null) {
      return '';
    }

    return value.slice(0, 16);
  }

  private toNullableApiDateTime(value: string, originalValue: string | null): string | null {
    if (value === '') {
      return null;
    }

    return this.toApiDateTime(value, originalValue);
  }

  /**
   * Converts a datetime-local value to an ISO-like datetime.
   *
   * This first version preserves the original UTC offset. If the edited date
   * crosses a daylight-saving boundary, the backend should ultimately resolve
   * the local time with the event timezone.
   */
  private toApiDateTime(value: string, originalValue: string | null): string {
    const offset = this.extractOffset(originalValue);

    return `${value}:00${offset}`;
  }

  private extractOffset(value: string | null): string {
    if (value === null) {
      return '';
    }

    const match = value.match(/([+-]\d{2}:\d{2}|Z)$/);

    return match?.[1] ?? '';
  }
}
