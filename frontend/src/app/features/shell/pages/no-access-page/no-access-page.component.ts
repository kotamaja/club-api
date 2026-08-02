import { Component } from '@angular/core';

import { ForbiddenStateComponent } from '../../../../shared/ui/forbidden-state/forbidden-state.component';

/**
 * Displays the no-access page used by route guards.
 *
 * Users are redirected here when they are authenticated but do not have the
 * required capability for a protected route.
 */
@Component({
  selector: 'app-no-access-page',
  imports: [ForbiddenStateComponent],
  templateUrl: './no-access-page.component.html',
  styleUrl: './no-access-page.component.scss',
})
export class NoAccessPageComponent {
}
