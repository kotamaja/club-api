import { Component, computed, inject, signal } from '@angular/core';
import { Router } from '@angular/router';

import { ClubAccess } from '../../../../core/context/club-access/club-access.model';
import { ClubAccessStoreService } from '../../../../core/context/club-access/club-access-store.service';
import { CurrentContext, CurrentContextState } from '../../../../core/context/current/current-context.model';
import { CurrentContextService } from '../../../../core/context/current/current-context.service';
import { ContextStorageService } from '../../../../core/context/storage/context-storage.service';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * State used by the club selection page.
 */
type SelectClubPageState =
  | { status: 'loading' }
  | { status: 'loaded'; context: CurrentContext; clubs: ClubAccess[] }
  | { status: 'empty' }
  | { status: 'error'; message: string };

/**
 * Displays the club selection page.
 *
 * This page is used when the current organization is resolved, several clubs
 * are available, and no current club could be restored automatically.
 */
@Component({
  selector: 'app-select-club-page',
  imports: [PageHeaderComponent],
  templateUrl: './select-club-page.component.html',
  styleUrl: './select-club-page.component.scss',
})
export class SelectClubPageComponent {
  private readonly currentContext = inject(CurrentContextService);
  private readonly clubAccessStore = inject(ClubAccessStoreService);
  private readonly contextStorage = inject(ContextStorageService);
  private readonly router = inject(Router);

  protected readonly state = computed<SelectClubPageState>(() => {
    const contextState = this.currentContext.state();

    if (contextState.status !== 'resolved') {
      return {
        status: 'error',
        message: 'Le contexte courant n’est pas disponible.',
      };
    }

    const clubs = this.clubAccessStore.clubs();

    if (clubs.length === 0) {
      return { status: 'empty' };
    }

    return {
      status: 'loaded',
      context: contextState.context,
      clubs,
    };
  });

  protected selectClub(context: CurrentContext, club: ClubAccess): void {
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

    void this.router.navigate(['/app/home'], {
      replaceUrl: true,
    });
  }

  protected goHome(): void {
    void this.router.navigate(['/app/home'], {
      replaceUrl: true,
    });
  }
}
