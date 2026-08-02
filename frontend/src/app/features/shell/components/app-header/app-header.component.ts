import { Component, computed, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { AuthService } from '../../../../core/auth/auth.service';
import { SessionService } from '../../../../core/auth/session.service';
import { CurrentContextService } from '../../../../core/context/current-context.service';

/**
 * Displays the connected application header.
 *
 * The current club is always shown. The current organization is only shown
 * when the user has access to multiple organizations.
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

  protected readonly currentUser = this.session.currentUser;
  protected readonly currentClub = this.currentContext.currentClub;
  protected readonly currentOrganization = this.currentContext.currentOrganization;

  protected readonly shouldShowOrganization = computed(() => {
    // Temporary placeholder. Later, this should come from OrganizationContextService.
    return false;
  });

  protected onLogout(): void {
    this.auth.logout().subscribe({
      next: () => {
        void this.router.navigate(['/login']);
      },
    });
}
}
