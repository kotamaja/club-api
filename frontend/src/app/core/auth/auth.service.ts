import { inject, Service } from '@angular/core';
import { map, Observable, tap } from 'rxjs';

import { ApiClientService } from '../api/api-client.service';
import { CurrentContextService } from '../context/current-context.service';
import { LoginResponse, PasswordLoginCommand } from './auth-api.model';
import { LoginResult } from './login-result.model';
import { SessionService } from './session.service';
import { TokenService } from './token.service';

/**
 * Coordinates authentication workflows.
 *
 * Password login is now delegated to the backend. Refresh-token handling will
 * be added separately, after the basic API connection is validated.
 */
@Service()
export class AuthService {
  private readonly api = inject(ApiClientService);
  private readonly currentContext = inject(CurrentContextService);
  private readonly session = inject(SessionService);
  private readonly token = inject(TokenService);

  loginWithPassword(email: string, password: string): Observable<LoginResult> {
    const command: PasswordLoginCommand = {
      email,
      password,
      refreshTokenMode: 'cookie',
    };

    return this.api.post<LoginResponse, PasswordLoginCommand>('/auth/login', command, {
      withCredentials: true,
    }).pipe(
      map((response): LoginResult => {
        return {
          accessToken: response.token,
          user: {
            id: email,
            email,
            displayName: email,
            status: 'active',
            roles: [],
          },
        };
      }),
      tap((result) => {
        this.token.setAccessToken(result.accessToken);
        this.session.setAuthenticated(result.user);

        // Temporary development context until /me/current-context is connected.
        this.currentContext.setDevelopmentContext();
      }),
    );
  }

  requestEmailCode(email: string): Observable<void> {
    // Temporary placeholder until the passwordless API exists.
    void email;

    return new Observable<void>((subscriber) => {
      subscriber.next();
      subscriber.complete();
    });
  }

  loginWithEmailCode(email: string, code: string): Observable<LoginResult> {
    // Temporary placeholder until the passwordless API exists.
    void email;
    void code;

    return new Observable<LoginResult>((subscriber) => {
      subscriber.error(new Error('Email-code login is not implemented yet.'));
    });
  }

  logout(): Observable<void> {
    this.token.clearAccessToken();
    this.session.setAnonymous();
    this.currentContext.reset();

    return this.api.post<void, Record<string, never>>('/auth/logout', {}, {
      withCredentials: true,
    });
  }
}
