import { Location } from '@angular/common';
import { Component, computed, effect, inject, signal } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';

import { ErrorStateComponent } from '../../../../shared/ui/error-state/error-state.component';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { EventDetailResource } from '../../data/detail/event-detail.resource';
import { EventDetail } from '../../data/detail/event-detail.schema';
import { EventEditForm, EventFormValidationError } from '../../data/form/event-form.model';
import {
  createEventEditForm,
  createEventUpdatePayload,
} from '../../data/form/event-form.mapper';
import { validateEventForm } from '../../data/form/event-form.validation';
import { eventStatusLabels, eventTypeLabels } from '../../data/options/event-labels';
import { EventWriteService } from '../../data/write/event-write.service';

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

    this.form.set(createEventEditForm(event));
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

  protected readonly validationErrors = computed<EventFormValidationError[]>(() => {
    const form = this.form();

    if (form === null) {
      return [];
    }

    return validateEventForm(form);
  });

  protected readonly isFormValid = computed(() => this.validationErrors().length === 0);

  protected readonly isDirty = computed(() => {
    const snapshot = this.eventResource.snapshot();
    const form = this.form();

    if (snapshot.status === 'error' || snapshot.status === 'loading' || snapshot.value === undefined || form === null) {
      return false;
    }

    return JSON.stringify(createEventUpdatePayload(snapshot.value, form)) !== JSON.stringify(createEventUpdatePayload(snapshot.value, createEventEditForm(snapshot.value)));
  });

  protected readonly canSave = computed(() => {
    return this.isFormValid()
      && this.isDirty()
      && this.saveState().status !== 'saving';
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

  protected updateCapacity(capacity: string): void {
    this.patchForm({ capacity });
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
    if (!this.canSave()) {
      return;
    }

    this.saveState.set({ status: 'saving' });

    this.eventWriteService.update(event.id, createEventUpdatePayload(event, form)).subscribe({
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
}
