import { inject, Service } from '@angular/core';
import { Observable } from 'rxjs';

import { ApiCollection } from '../../api/api-collection.model';
import { ApiClientService } from '../../api/api-client.service';
import { ClubAccess } from './club-access.model';

/**
 * Loads the clubs available to the authenticated user inside one organization.
 */
@Service()
export class ClubAccessService {
  private readonly api = inject(ApiClientService);

  getClubs(organizationId: string): Observable<ApiCollection<ClubAccess>> {
    return this.api.get<ApiCollection<ClubAccess>>(`/me/organizations/${organizationId}/clubs`);
  }
}
