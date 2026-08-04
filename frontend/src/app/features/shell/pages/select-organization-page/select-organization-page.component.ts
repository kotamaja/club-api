import { Component, inject, signal } from '@angular/core';
import { Router } from '@angular/router';

import { ContextStorageService } from '../../../../core/context/storage/context-storage.service';
import { CurrentContextService } from '../../../../core/context/current/current-context.service';
import { OrganizationAccess } from '../../../../core/context/organization-access/organization-access.model';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { ContextFactoryService } from '../../../../core/context/resolution/context-factory.service';
import {
  OrganizationAccessStoreService
} from '../../../../core/context/organization-access/organization-access-store.service';

/**
 * State used by the organization selection page.
 */
type SelectOrganizationPageState =
  | { status: 'loading' }
  | { status: 'loaded'; organizations: OrganizationAccess[] }
  | { status: 'empty' }
  | { status: 'error'; message: string };

/**
 * Displays the organization selection page.
 *
 * Users with access to several organizations can choose which organization
 * they want to use for the current application context.
 */
@Component({
  selector: 'app-select-organization-page',
  imports: [PageHeaderComponent],
  templateUrl: './select-organization-page.component.html',
  styleUrl: './select-organization-page.component.scss',
})
export class SelectOrganizationPageComponent {
  private readonly contextStorage = inject(ContextStorageService);
  private readonly currentContext = inject(CurrentContextService);
  private readonly organizationAccessStore = inject(OrganizationAccessStoreService);
  private readonly router = inject(Router);
  private readonly contextFactory = inject(ContextFactoryService);

  protected readonly state = signal<SelectOrganizationPageState>({ status: 'loading' });

  constructor() {
    this.loadOrganizations();
  }

  protected selectOrganization(organization: OrganizationAccess): void {
    const context = this.contextFactory.createFromOrganizationAccess(organization);

    this.currentContext.setContext(context);
    this.contextStorage.save({
      organizationId: context.organization.id,
      clubId: null,
    });

    void this.router.navigate(['/app/home'], {
      replaceUrl: true,
    });
  }

  private loadOrganizations(): void {
    this.state.set({ status: 'loading' });

    this.organizationAccessStore.load().subscribe({
      next: (organizations) => {
        const enabledOrganizations = organizations.filter((organization) => organization.enabled);

        if (enabledOrganizations.length === 0) {
          this.state.set({ status: 'empty' });

          return;
        }

        this.state.set({
          status: 'loaded',
          organizations: enabledOrganizations,
        });
      },
      error: () => {
        this.state.set({
          status: 'error',
          message: 'Impossible de charger les organisations disponibles.',
        });
      },
    });
  }

}
