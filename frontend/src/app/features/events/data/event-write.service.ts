import { inject, Service } from '@angular/core';
import { map, Observable } from 'rxjs';

import { ApiClientService } from '../../../core/api/api-client.service';
import { EventDetail, EventDetailSchema } from './event-detail.schema';
import { EventUpdatePayload } from './event-update.model';

/**
 * Handles event write operations.
 */
@Service()
export class EventWriteService {
  private readonly api = inject(ApiClientService);

  /**
   * Updates an event and returns the updated detail.
   */
  update(eventId: string, payload: EventUpdatePayload): Observable<EventDetail> {
    return this.api.patch<unknown>(`/events/${eventId}`, payload).pipe(
      map((response) => EventDetailSchema.parse(response)),
    );
  }
}
