import { Component, inject } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';

import { CapabilityService } from '../../../../core/capabilities/capability.service';

/**
 * Displays the main desktop navigation.
 *
 * Navigation entries are prepared to be shown according to user capabilities.
 */
@Component({
  selector: 'app-desktop-sidebar',
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './desktop-sidebar.component.html',
  styleUrl: './desktop-sidebar.component.scss',
})
export class DesktopSidebarComponent {
  private readonly capability = inject(CapabilityService);

  protected canAccessMemberArea(): boolean {
    return this.capability.can('canAccessMemberArea');
  }

  protected canAccessManagementArea(): boolean {
    return this.capability.can('canAccessManagementArea');
  }

  protected canManageMembers(): boolean {
    return this.capability.can('canManageMembers');
  }

  protected canManageEvents(): boolean {
    return this.capability.can('canManageEvents');
  }
}
