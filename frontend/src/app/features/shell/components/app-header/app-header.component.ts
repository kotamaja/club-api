import { Component, computed, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { AuthService } from '../../../../core/auth/services/auth.service';
import { SessionService } from '../../../../core/auth/session/session.service';
import { ClubAccess } from '../../../../core/context/club-access/club-access.model';
import { ClubAccessStoreService } from '../../../../core/context/club-access/club-access-store.service';
import { CurrentContext } from '../../../../core/context/current/current-context.model';
import { CurrentContextService } from '../../../../core/context/current/current-context.service';
import { OrganizationAccessStoreService } from '../../../../core/context/organization-access/organization-access-store.service';
import { ContextStorageService } from '../../../../core/context/storage/context-storage.service';

/**
 * Displays the connected application header.
 *
 * The current organization is only shown when the user has access to multiple
 * enabled organizations. The current club is shown when it exists, and becomes
 * selectable when several clubs are available.
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
  private readonly contextStorage = inject(ContextStorageService);

  protected readonly currentUser = this.session.currentUser;
  protected readonly currentClub = this.currentContext.currentClub;
  protected readonly currentOrganization = this.currentContext.currentOrganization;
  protected readonly availableClubs = this.clubAccessStore.clubs;

  protected readonly shouldShowOrganization = computed(() => {
    return this.organizationAccessStore.enabledOrganizations().length > 1;
  });

  protected readonly shouldShowReadonlyClub = computed(() => {
    return this.availableClubs().length === 1 && this.currentClub() !== null;
  });

  protected readonly shouldShowClubSelect = computed(() => {
    return this.availableClubs().length > 1;
  });

  protected selectClub(clubId: string): void {
    const state = this.currentContext.state();

    if (state.status !== 'resolved') {
      return;
    }

    const selectedClub = this.availableClubs().find((club) => club.clubId === clubId);

    if (selectedClub === undefined) {
      return;
    }

    this.setCurrentClub(state.context, selectedClub)

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
}
