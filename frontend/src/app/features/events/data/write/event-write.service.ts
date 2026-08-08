import { inject, Service } from '@angular/core';
import { map, Observable } from 'rxjs';

import { ApiClientService } from '../../../../core/api/api-client.service';
import { EventCreatePayload } from './event-create.model';
import { EventDetail, EventDetailSchema } from '../detail/event-detail.schema';
import { EventUpdatePayload } from './event-update.model';

/**
 * Handles event write operations.
 */
@Service()
export class EventWriteService {
  private readonly api = inject(ApiClientService);

  /**
   * Creates an event and returns the created detail.
   */
  create(payload: EventCreatePayload): Observable<EventDetail> {
    return this.api.post<unknown>('/events', payload).pipe(
      map((response) => EventDetailSchema.parse(response)),
    );
  }

  /**
   * Updates an event and returns the updated detail.
   */
  update(eventId: string, payload: EventUpdatePayload): Observable<EventDetail> {
    return this.api.patch<unknown>(`/events/${eventId}`, payload).pipe(
      map((response) => EventDetailSchema.parse(response)),
    );
  }
}
