import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';

/**
 * Displays the authentication entry page.
 *
 * This page introduces the available login methods. Password login is expected
 * first, while email-code login is prepared for the near future.
 */
@Component({
  selector: 'app-login-page',
  imports: [RouterLink, PageHeaderComponent],
  templateUrl: './login-page.component.html',
  styleUrl: './login-page.component.scss',
})
export class LoginPageComponent {
}
