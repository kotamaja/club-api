import { Component, computed, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { AuthService } from '../../../../core/auth/services/auth.service';
import { SessionService } from '../../../../core/auth/session/session.service';
import { CurrentContextService } from '../../../../core/context/current/current-context.service';
import { OrganizationAccessStoreService } from '../../../../core/context/organization-access/organization-access-store.service';

/**
 * Displays the connected application header.
 *
 * The current organization is only shown when the user has access to multiple
 * enabled organizations. The current club is shown when it exists.
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

  protected readonly currentUser = this.session.currentUser;
  protected readonly currentClub = this.currentContext.currentClub;
  protected readonly currentOrganization = this.currentContext.currentOrganization;

  protected readonly shouldShowOrganization = computed(() => {
    return this.organizationAccessStore.enabledOrganizations().length > 1;
  });

  protected onLogout(): void {
    this.auth.logout().subscribe({
      next: () => {
        void this.router.navigate(['/login']);
      },
    });
}
}
