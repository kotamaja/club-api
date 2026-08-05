import { httpResource } from '@angular/common/http';
import { inject, Service, Signal } from '@angular/core';

import { ApiUrlService } from '../../../core/api/api-url.service';
import { EventDetailSchema } from './event-detail.schema';

/**
 * Creates a reactive HTTP resource for an event detail page.
 */
@Service()
export class EventDetailResource {
  private readonly apiUrl = inject(ApiUrlService);

  create(eventId: Signal<string | null>) {
    return httpResource(() => {
      const id = eventId();

      if (id === null) {
        return undefined;
      }

      return {
        url: this.apiUrl.build(`/events/${id}`),
      };
    }, {
      parse: EventDetailSchema.parse,
    });
  }
}
