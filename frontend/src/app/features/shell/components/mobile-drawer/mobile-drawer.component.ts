import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';

/**
 * Placeholder for the mobile navigation drawer.
 *
 * The real drawer behavior can later be implemented with Angular Material
 * sidenav or another CDK-based approach.
 */
@Component({
  selector: 'app-mobile-drawer',
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './mobile-drawer.component.html',
  styleUrl: './mobile-drawer.component.scss',
})
export class MobileDrawerComponent {
}
