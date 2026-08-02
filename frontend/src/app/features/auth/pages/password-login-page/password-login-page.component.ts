import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { PageHeaderComponent } from '../../../../shared/ui/page-header/page-header.component';
import { PasswordLoginFormComponent } from '../../components/password-login-form/password-login-form.component';

/**
 * Displays the password login page.
 *
 * The page hosts the password login form and redirects the user to the
 * connected application after successful authentication.
 */
@Component({
  selector: 'app-password-login-page',
  imports: [RouterLink, PageHeaderComponent, PasswordLoginFormComponent],
  templateUrl: './password-login-page.component.html',
  styleUrl: './password-login-page.component.scss',
})
export class PasswordLoginPageComponent {
  private readonly router = inject(Router);

  protected onLoggedIn(): void {
    void this.router.navigate(['/app']);
  }
}
