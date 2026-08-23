import {inject, Service} from '@angular/core';
import { Observable, map } from 'rxjs';

import { ApiClientService } from '../../../../core/api/api-client.service';
import { EventRegistrationListItem, EventRegistrationListItemSchema } from '../list/event-registration-list.schema';
import { EventRegistrationCreatePayload } from './event-registration-create.model';

/**
 * Performs write operations on event registrations.
 */
@Service()
export class EventRegistrationWriteService {
  private readonly api = inject(ApiClientService);

  create(eventId: string, payload: EventRegistrationCreatePayload): Observable<EventRegistrationListItem> {
    return this.api.post<unknown>(`/events/${eventId}/registrations`, payload).pipe(
      map((value) => EventRegistrationListItemSchema.parse(value)),
    );
  }

  cancel(registrationId: string): Observable<EventRegistrationListItem> {
    return this.api.post<unknown>(`/event-registrations/${registrationId}/cancel`, {}).pipe(
      map((value) => EventRegistrationListItemSchema.parse(value)),
    );
  }
}
