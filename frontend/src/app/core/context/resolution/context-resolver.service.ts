import { inject, Service } from '@angular/core';
import {map, Observable, of, switchMap, tap} from 'rxjs';

import { ClubAccess } from '../club-access/club-access.model';
import { ClubAccessStoreService } from '../club-access/club-access-store.service';
import { CurrentContext } from '../current/current-context.model';
import { CurrentContextService } from '../current/current-context.service';
import { OrganizationAccess } from '../organization-access/organization-access.model';
import { OrganizationAccessStoreService } from '../organization-access/organization-access-store.service';
import { ContextStorageService } from '../storage/context-storage.service';
import { ContextFactoryService } from './context-factory.service';
import { ContextResolution } from './context-resolution.model';

/**
 * Resolves the current organization and club context for the application.
 *
 * The resolver restores the last selected organization and club when they are
 * still available. If no club can be selected automatically, the organization
 * context is still resolved with a null club.
 */
@Service()
export class ContextResolverService {
  private readonly contextStorage = inject(ContextStorageService);
  private readonly currentContext = inject(CurrentContextService);
  private readonly contextFactory = inject(ContextFactoryService);
  private readonly organizationAccessStore = inject(OrganizationAccessStoreService);
  private readonly clubAccessStore = inject(ClubAccessStoreService);

  resolve(): Observable<ContextResolution> {

    const storedSelection = this.contextStorage.get();

    return this.organizationAccessStore.load().pipe(
      map((organizations) => {
        const enabledOrganizations = organizations.filter((organization) => organization.enabled);

        if (enabledOrganizations.length === 0) {
          return {
            resolution: { status: 'noAccess' } satisfies ContextResolution,
            organization: null,
            storedClubId: null,
          };
        }

        if (storedSelection !== null) {
          const storedOrganization = enabledOrganizations.find((organization) => {
            return organization.organizationId === storedSelection.organizationId;
          });

          if (storedOrganization !== undefined) {
            return {
              resolution: { status: 'resolved' } satisfies ContextResolution,
              organization: storedOrganization,
              storedClubId: storedSelection.clubId,
            };
          }
        }

        if (enabledOrganizations.length === 1) {
          return {
            resolution: { status: 'resolved' } satisfies ContextResolution,
            organization: enabledOrganizations[0],
            storedClubId: null,
          };
        }

        return {
          resolution: { status: 'needsSelection' } satisfies ContextResolution,
          organization: null,
          storedClubId: null,
        };
      }),
      switchMap((result) => {
        if (result.organization === null) {
          return of({
            ...result,
            context: null,
          });
        }



        return this.clubAccessStore.load(result.organization.organizationId).pipe(
          map((clubs) => {
            return {
            ...result,
            context: this.createContext(result.organization, clubs, result.storedClubId),
            };
          }),
        );
      }),
      tap((result) => {
        if (result.context !== null) {
          this.currentContext.setContext(result.context);
          this.contextStorage.save({
            organizationId: result.context.organization.id,
            clubId: result.context.club?.id ?? null,
          });

          return;
        }

        this.currentContext.setMissing();
      }),
      map((result) => result.resolution),
    );
  }

  private createContext(organization: OrganizationAccess, clubs: ClubAccess[], storedClubId: string | null): CurrentContext {
    const selectedClub = this.resolveClub(clubs, storedClubId);

    return this.contextFactory.createFromOrganizationAccess(organization, selectedClub);
  }

  private resolveClub(clubs: ClubAccess[], storedClubId: string | null): ClubAccess | null {
    if (storedClubId !== null) {
      const storedClub = clubs.find((club) => club.clubId === storedClubId);

      if (storedClub !== undefined) {
        return storedClub;
      }
    }

    if (clubs.length === 1) {
      return clubs[0];
    }

    return null;
  }
}
