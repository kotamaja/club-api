import { inject, Service } from '@angular/core';
import { Observable } from 'rxjs';

import { ApiClientService } from '../../api/api-client.service';
import { Me } from './me.model';

/**
 * Loads information about the currently authenticated user.
 *
 * This service is mainly used to validate the authenticated API connection
 * before the full session restoration flow is implemented.
 */
@Service()
export class MeService {
  private readonly api = inject(ApiClientService);

  getMe(): Observable<Me> {
    return this.api.get<Me>('/me');
  }
}
