import { Component, inject } from '@angular/core';
import { RouterLink } from '@angular/router';

import { CapabilityService } from '../../../../core/capabilities/capability.service';
import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * Displays the connected application home page.
 *
 * This page lets authenticated users choose between their member area and
 * the club management area.
 */
@Component({
  selector: 'app-app-home-page',
  imports: [RouterLink, PageHeaderComponent],
  templateUrl: './app-home-page.component.html',
  styleUrl: './app-home-page.component.scss',
})
export class AppHomePageComponent {
  private readonly capability = inject(CapabilityService);

  protected canAccessMemberArea(): boolean {
    return this.capability.can('canAccessMemberArea');
}

  protected canAccessManagementArea(): boolean {
    return this.capability.can('canAccessManagementArea');
  }
}
