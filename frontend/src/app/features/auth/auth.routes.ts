import { Routes } from '@angular/router';

import { EmailCodeRequestPageComponent } from './pages/email-code-request-page/email-code-request-page.component';
import { EmailCodeVerifyPageComponent } from './pages/email-code-verify-page/email-code-verify-page.component';
import { LoginPageComponent } from './pages/login-page/login-page.component';
import { PasswordLoginPageComponent } from './pages/password-login-page/password-login-page.component';

/**
 * Defines the public authentication routes.
 *
 * Password login is expected in V1, while email-code login is prepared for
 * the near future.
 */
export const authRoutes: Routes = [
  {
    path: '',
    component: LoginPageComponent,
  },
  {
    path: 'password',
    component: PasswordLoginPageComponent,
  },
  {
    path: 'email-code',
    component: EmailCodeRequestPageComponent,
  },
  {
    path: 'email-code/verify',
    component: EmailCodeVerifyPageComponent,
  },
];
