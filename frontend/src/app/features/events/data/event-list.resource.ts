import { httpResource } from '@angular/common/http';
import {  inject, Service, Signal } from '@angular/core';

import { ApiUrlService } from '../../../core/api/api-url.service';
import { EventListQuery } from './event-list-query.model';
import {EventListPage, EventListPageSchema} from './event.schema';

/**
 * Creates a reactive HTTP resource for the event management list.
 *
 * The resource reloads automatically whenever the supplied query signal
 * changes. API headers are added by the global API interceptors.
 */
@Service()
export class EventListResource {
  private readonly apiUrl = inject(ApiUrlService);

  create(query: Signal<EventListQuery>) {
    return httpResource(() => ({
      url: this.apiUrl.build('/events'),
      params: this.toParams(query()),
    }), {
      parse: EventListPageSchema.parse,
      defaultValue: {
        items: [],
        page: 1,
        perPage: 20,
        totalItems: 0,
      } satisfies EventListPage,
    });
  }

  private toParams(query: EventListQuery): Record<string, string | number | boolean> {
    return {
      pagination: true,
      page: query.page,
      perPage: query.perPage,
      sort: query.sort,
      direction: query.direction,
      ...this.toSearchParam(query),
      ...this.toStatusParam(query),
      ...this.toPeriodParams(query),
    };
  }

  private toSearchParam(query: EventListQuery): Record<string, string> {
    if (query.search.trim() === '') {
      return {};
    }

    return {
      search: query.search.trim(),
    };
  }

  private toStatusParam(query: EventListQuery): Record<string, string> {
    if (query.status === 'all') {
      return {};
    }

    return {
      status: query.status,
    };
  }

  private toPeriodParams(query: EventListQuery): Record<string, string> {
    const now = new Date().toISOString();

    if (query.period === 'upcoming') {
      return {
        'startsAt[after]': now,
      };
    }

    if (query.period === 'past') {
      return {
        'startsAt[before]': now,
      };
    }

    return {};
  }
}
