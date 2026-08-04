import { inject, Service } from '@angular/core';
import { map, Observable, tap } from 'rxjs';


import { ContextResolution } from './context-resolution.model';
import { ContextStorageService } from '../storage/context-storage.service';
import { CurrentContextService } from '../current/current-context.service';
import { ContextFactoryService } from './context-factory.service';
import {OrganizationAccessStoreService} from '../organization-access/organization-access-store.service';

/**
 * Resolves the current organization and club context for the application.
 *
 * The resolver restores the last selected organization when it is still
 * available, otherwise it asks the UI to show a selection page.
 */
@Service()
export class ContextResolverService {
  private readonly contextStorage = inject(ContextStorageService);
  private readonly currentContext = inject(CurrentContextService);
  private readonly contextFactory = inject(ContextFactoryService);
  private readonly organizationAccessStore = inject(OrganizationAccessStoreService);

  resolve(): Observable<ContextResolution> {
    return this.organizationAccessStore.load().pipe(
      map((organizations) => {
        const enabledOrganizations = organizations.filter((organization) => organization.enabled);

        if (enabledOrganizations.length === 0) {
          return {
            resolution: { status: 'noAccess' } satisfies ContextResolution,
            context: null,
          };
        }

        const storedSelection = this.contextStorage.get();

        if (storedSelection !== null) {
          const storedOrganization = enabledOrganizations.find((organization) => {
            return organization.organizationId === storedSelection.organizationId;
          });

          if (storedOrganization !== undefined) {
            return {
              resolution: { status: 'resolved' } satisfies ContextResolution,
              context: this.contextFactory.createFromOrganizationAccess(storedOrganization),
            };
          }
        }

        if (enabledOrganizations.length === 1) {
          return {
            resolution: { status: 'resolved' } satisfies ContextResolution,
            context: this.contextFactory.createFromOrganizationAccess(enabledOrganizations[0]),
          };
        }

        return {
          resolution: { status: 'needsSelection' } satisfies ContextResolution,
          context: null,
        };
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
}
