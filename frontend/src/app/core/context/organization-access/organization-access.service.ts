import { inject, Service } from '@angular/core';
import { Observable } from 'rxjs';

import { ApiCollection } from '../../api/api-collection.model';
import { ApiClientService } from '../../api/api-client.service';
import { OrganizationAccess } from './organization-access.model';

/**
 * Loads the organizations available to the authenticated user.
 *
 * The backend returns a non-paginated collection because this endpoint is used
 * for context selection rather than for a management list.
 */
@Service()
export class OrganizationAccessService {
  private readonly api = inject(ApiClientService);

  getOrganizations(): Observable<ApiCollection<OrganizationAccess>> {
    return this.api.get<ApiCollection<OrganizationAccess>>('/me/organizations');
  }
}
