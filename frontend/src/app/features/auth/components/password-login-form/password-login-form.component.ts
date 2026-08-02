import { Component, inject, output, signal } from '@angular/core';
import { FormField, form, email, required, submit } from '@angular/forms/signals';

import { AuthService } from '../../../../core/auth/auth.service';
import { PasswordLoginFormModel } from './password-login-form.model';

/**
 * Displays the password login form.
 *
 * This component owns the Signal Form state and delegates the authentication
 * workflow to AuthService.
 */
@Component({
  selector: 'app-password-login-form',
  imports: [FormField],
  templateUrl: './password-login-form.component.html',
  styleUrl: './password-login-form.component.scss',
})
export class PasswordLoginFormComponent {
  private readonly auth = inject(AuthService);

  readonly loggedIn = output<void>();

  protected readonly model = signal<PasswordLoginFormModel>({
    email: '',
    password: '',
  });

  protected readonly loginForm = form(this.model, (path) => {
    required(path.email, { message: 'L’adresse email est obligatoire.' });
    email(path.email, { message: 'Veuillez saisir une adresse email valide.' });
    required(path.password, { message: 'Le mot de passe est obligatoire.' });
  });

  protected onSubmit(event: Event): void {
    event.preventDefault();

    void submit(this.loginForm, {
      action: async () => {
        const credentials = this.model();

        await new Promise<void>((resolve, reject) => {
          this.auth.loginWithPassword(credentials.email, credentials.password).subscribe({
            next: () => {
              this.loggedIn.emit();
              resolve();
            },
            error: () => {
              reject();
            },
          });
        });
      },
    });
  }
}
