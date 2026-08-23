import { HttpParams } from '@angular/common/http';
import {computed, inject, Service, Signal} from '@angular/core';
import { httpResource } from '@angular/common/http';

import { ApiUrlService } from '../../../../core/api/api-url.service';
import { EventRegistrationListQuery } from './event-registration-list-query.model';
import { EventRegistrationListPage, EventRegistrationListPageSchema } from './event-registration-list.schema';

/**
 * Loads event registrations for one event using the management API.
 */
@Service()
export class EventRegistrationListResource {
  private readonly apiUrl = inject(ApiUrlService);

  create(eventId: Signal<string>, query: Signal<EventRegistrationListQuery>) {
    return httpResource<EventRegistrationListPage>(() => {
      const currentEventId = eventId();
      const currentQuery = query();

      return {
        url: this.apiUrl.build(`/events/${currentEventId}/registrations`),
        params: this.createParams(currentQuery),
      };
    }, {
      parse: (value) => EventRegistrationListPageSchema.parse(value),
    });
  }

  private createParams(query: EventRegistrationListQuery): HttpParams {
    let params = new HttpParams()
      .set('page', query.page)
      .set('itemsPerPage', query.perPage);

    if (query.status !== 'all') {
      params = params.set('status', query.status);
    }

    return params.set(this.toSortParameter(query.sort), query.direction);
  }

  private toSortParameter(sort: EventRegistrationListQuery['sort']): string {
    switch (sort) {
      case 'personLastname':
        return 'orderPersonLastname';

      case 'status':
        return 'orderStatus';

      case 'requestedAt':
        return 'orderRequestedAt';

      case 'cancelledAt':
        return 'orderCancelledAt';
    }
  }
}
