import { Location } from '@angular/common';
import { Component, computed, inject, signal } from '@angular/core';
import { Router } from '@angular/router';

import { ErrorStateComponent } from '../../../../shared/ui/error-state/error-state.component';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import {
  createDefaultEventForm,
  createEventCreatePayload,
} from '../../data/form/event-form.mapper';
import { EventFormBase, EventFormValidationError } from '../../data/form/event-form.model';
import { validateEventForm } from '../../data/form/event-form.validation';
import { eventTypeLabels } from '../../data/options/event-labels';
import { EventType } from '../../data/shared/event.schema';
import { EventWriteService } from '../../data/write/event-write.service';

/**
 * Save state used by the event creation page.
 */
type EventCreateSaveState =
  | { status: 'idle' }
  | { status: 'saving' }
  | { status: 'saved' }
  | { status: 'error'; message: string };

/**
 * Displays the event creation form.
 */
@Component({
  selector: 'app-event-create-page',
  imports: [
    ErrorStateComponent,
    PageHeaderComponent,
  ],
  templateUrl: './event-create-page.component.html',
  styleUrl: './event-create-page.component.scss',
})
export class EventCreatePageComponent {
  private readonly router = inject(Router);
  private readonly location = inject(Location);
  private readonly eventWriteService = inject(EventWriteService);

  protected readonly form = signal<EventFormBase>(createDefaultEventForm());
  protected readonly saveState = signal<EventCreateSaveState>({ status: 'idle' });

  protected readonly saveErrorMessage = computed(() => {
    const state = this.saveState();

    return state.status === 'error' ? state.message : null;
  });

  protected readonly validationErrors = computed<EventFormValidationError[]>(() => {
    return validateEventForm(this.form());
  });

  protected readonly isFormValid = computed(() => this.validationErrors().length === 0);

  protected readonly canSave = computed(() => {
    return this.isFormValid()
      && this.saveState().status !== 'saving';
  });

  protected goBack(): void {
    this.location.back();
  }

  protected updateTitle(title: string): void {
    this.patchForm({ title });
  }

  protected updateDescription(description: string): void {
    this.patchForm({ description });
  }

  protected updateType(eventType: EventType): void {
    this.patchForm({ eventType });
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

  protected save(): void {
    if (!this.canSave()) {
      return;
    }

    this.saveState.set({ status: 'saving' });

    this.eventWriteService.create(createEventCreatePayload(this.form())).subscribe({
      next: (createdEvent) => {
        this.saveState.set({ status: 'saved' });

        void this.router.navigate(['/app/manage/events', createdEvent.id], {
          replaceUrl: true,
        });
      },
      error: () => {
        this.saveState.set({
          status: 'error',
          message: 'Impossible de créer l’événement.',
        });
      },
    });
  }

  protected eventTypeLabel(eventType: EventType): string {
    return eventTypeLabels[eventType] ?? eventType;
  }

  private patchForm(patch: Partial<EventFormBase>): void {
    this.form.update((form) => ({
      ...form,
      ...patch,
    }));

    if (this.saveState().status === 'saved' || this.saveState().status === 'error') {
      this.saveState.set({ status: 'idle' });
    }
  }
}
