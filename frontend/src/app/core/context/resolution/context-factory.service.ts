import { Service } from '@angular/core';

import { Capability } from '../../capabilities/capability.model';
import { CurrentContext } from '../current/current-context.model';
import { OrganizationAccess } from '../organization-access/organization-access.model';

/**
 * Creates frontend context objects from backend organization access data.
 *
 * This service centralizes the temporary mapping used until the backend
 * exposes the final current-context endpoint with clubs and capabilities.
 */
@Service()
export class ContextFactoryService {
  createFromOrganizationAccess(organization: OrganizationAccess): CurrentContext {
    return {
      organization: {
        id: organization.organizationId,
        name: organization.organizationName,
      },
      club: null,

      // Temporary capabilities until the backend exposes them from the current context endpoint.
      capabilities: this.createDevelopmentCapabilities(),
    };
  }

  private createDevelopmentCapabilities(): Capability[] {
    return [
      'canAccessMemberArea',
      'canAccessManagementArea',
      'canManageMembers',
      'canManageMemberships',
      'canManageEvents',
      'canManageEventRegistrations',
      'canReviewPublicRegistrationRequests',
      'canManageClubSettings',
    ];
  }
}
