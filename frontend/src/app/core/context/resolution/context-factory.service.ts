import { Service } from '@angular/core';

import { Capability } from '../../capabilities/capability.model';
import { CurrentClubSummary, CurrentContext } from '../current/current-context.model';
import { ClubAccess } from '../club-access/club-access.model';
import { OrganizationAccess } from '../organization-access/organization-access.model';

/**
 * Creates frontend context objects from backend organization and club access data.
 *
 * This service centralizes the temporary mapping used until the backend
 * exposes the final current-context endpoint with capabilities.
 */
@Service()
export class ContextFactoryService {
  createFromOrganizationAccess(organization: OrganizationAccess, club: ClubAccess | null = null): CurrentContext {
    return {
      organization: {
        id: organization.organizationId,
        name: organization.organizationName,
      },
      club: club === null ? null : this.createClubSummary(club),

      // Temporary capabilities until the backend exposes them from the current context endpoint.
      capabilities: this.createDevelopmentCapabilities(),
    };
  }

  private createClubSummary(club: ClubAccess): CurrentClubSummary {
    return {
      id: club.clubId,
      name: club.clubName,
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
