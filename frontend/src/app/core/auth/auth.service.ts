import { inject, Service } from '@angular/core';
import { Observable, of } from 'rxjs';

import { CurrentContextService } from '../context/current-context.service';
import { LoginResult } from './login-result.model';
import { SessionService } from './session.service';
import { TokenService } from './token.service';

/**
 * Coordinates authentication workflows.
 *
 * The password login is expected first. Email-code login is intentionally
 * represented here because it should be added soon.
 */
@Service()
export class AuthService {
  private readonly currentContext = inject(CurrentContextService);
  private readonly session = inject(SessionService);
  private readonly token = inject(TokenService);

  loginWithPassword(email: string, password: string): Observable<LoginResult> {
    // Temporary placeholder until the API call is wired.
    void password;

    const result: LoginResult = {
      accessToken: 'temporary-access-token',
      user: {
        id: 'temporary-user-id',
        email,
        displayName: email,
      },
    };

    this.token.setAccessToken(result.accessToken);
    this.session.setAuthenticated(result.user);
    this.currentContext.setDevelopmentContext();

    return of(result);
  }

  requestEmailCode(email: string): Observable<void> {
    // Temporary placeholder until the passwordless API exists.
    void email;

    return of(void 0);
  }

  loginWithEmailCode(email: string, code: string): Observable<LoginResult> {
    // Temporary placeholder until the passwordless API exists.
    void code;

    const result: LoginResult = {
      accessToken: 'temporary-access-token',
      user: {
        id: 'temporary-user-id',
        email,
        displayName: email,
      },
    };

    this.token.setAccessToken(result.accessToken);
    this.session.setAuthenticated(result.user);
    console.log("auth.service")
    this.currentContext.setDevelopmentContext();

    return of(result);
  }

  logout(): Observable<void> {
    this.token.clearAccessToken();
    this.session.setAnonymous();
    this.currentContext.reset();

    return of(void 0);
  }
}
