import { Component, computed, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { AuthService } from '../../../../core/auth/services/auth.service';
import { SessionService } from '../../../../core/auth/session/session.service';
import { ClubAccess } from '../../../../core/context/club-access/club-access.model';
import { ClubAccessStoreService } from '../../../../core/context/club-access/club-access-store.service';
import { CurrentContext } from '../../../../core/context/current/current-context.model';
import { CurrentContextService } from '../../../../core/context/current/current-context.service';
import { OrganizationAccess } from '../../../../core/context/organization-access/organization-access.model';
import { OrganizationAccessStoreService } from '../../../../core/context/organization-access/organization-access-store.service';
import { ContextFactoryService } from '../../../../core/context/resolution/context-factory.service';
import { ContextStorageService } from '../../../../core/context/storage/context-storage.service';

/**
 * Displays the connected application header.
 *
 * The organization selector is shown only when several organizations are
 * available. The club selector is shown only when several clubs are available.
 */
@Component({
  selector: 'app-app-header',
  imports: [RouterLink],
  templateUrl: './app-header.component.html',
  styleUrl: './app-header.component.scss',
})
export class AppHeaderComponent {
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);
  private readonly session = inject(SessionService);
  private readonly currentContext = inject(CurrentContextService);
  private readonly organizationAccessStore = inject(OrganizationAccessStoreService);
  private readonly clubAccessStore = inject(ClubAccessStoreService);
  private readonly contextFactory = inject(ContextFactoryService);
  private readonly contextStorage = inject(ContextStorageService);

  protected readonly currentUser = this.session.currentUser;
  protected readonly currentClub = this.currentContext.currentClub;
  protected readonly currentOrganization = this.currentContext.currentOrganization;
  protected readonly availableClubs = this.clubAccessStore.clubs;
  protected readonly availableOrganizations = this.organizationAccessStore.enabledOrganizations;

  protected readonly shouldShowOrganizationSelect = computed(() => {
    return this.availableOrganizations().length > 1;
  });

  protected readonly shouldShowReadonlyClub = computed(() => {
    return this.availableClubs().length === 1 && this.currentClub() !== null;
  });

  protected readonly shouldShowClubSelect = computed(() => {
    return this.availableClubs().length > 1;
  });

  protected selectOrganization(organizationId: string): void {
    const selectedOrganization = this.availableOrganizations().find((organization) => {
      return organization.organizationId === organizationId;
    });

    if (selectedOrganization === undefined) {
      return;
    }

    this.clubAccessStore.reload(selectedOrganization.organizationId).subscribe({
      next: (clubs) => {
        const selectedClub = this.resolveClub(clubs, null);
        const context = this.contextFactory.createFromOrganizationAccess(selectedOrganization, selectedClub);

        this.currentContext.setContext(context);
        this.contextStorage.save({
          organizationId: context.organization.id,
          clubId: context.club?.id ?? null,
        });

        void this.router.navigate(['/app/home'], {
          replaceUrl: true,
        });
      },
    });
  }

  protected selectClub(clubId: string): void {
    const state = this.currentContext.state();

    if (state.status !== 'resolved') {
      return;
    }

    const selectedClub = this.availableClubs().find((club) => {
      return club.clubId === clubId;
    });

    if (selectedClub === undefined) {
      return;
    }

    this.setCurrentClub(state.context, selectedClub);

    void this.router.navigate(['/app/home'], {
      replaceUrl: true,
    });
  }

  protected onLogout(): void {
    this.auth.logout().subscribe({
      next: () => {
        void this.router.navigate(['/login']);
      },
    });
  }

  private setCurrentClub(context: CurrentContext, club: ClubAccess): void {
    const nextContext: CurrentContext = {
      ...context,
      club: {
        id: club.clubId,
        name: club.clubName,
      },
    };

    this.currentContext.setContext(nextContext);
    this.contextStorage.save({
      organizationId: nextContext.organization.id,
      clubId: nextContext.club?.id ?? null,
    });
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
