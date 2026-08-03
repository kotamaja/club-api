import { inject, Service } from '@angular/core';
import { map, Observable, switchMap, tap } from 'rxjs';

import { ApiClientService } from '../api/api-client.service';
import { CurrentContextService } from '../context/current-context.service';
import { LoginResponse, PasswordLoginCommand } from './auth-api.model';
import { CurrentUser } from './current-user.model';
import { LoginResult } from './login-result.model';
import { Me } from './me.model';
import { MeService } from './me.service';
import { SessionService } from './session.service';
import { TokenService } from './token.service';

/**
 * Coordinates authentication workflows.
 *
 * Password login is delegated to the backend. The refresh token is managed
 * through a backend cookie, while Angular keeps only the access token.
 */
@Service()
export class AuthService {
  private readonly api = inject(ApiClientService);
  private readonly currentContext = inject(CurrentContextService);
  private readonly me = inject(MeService);
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
      tap((response) => {
        this.token.setAccessToken(response.token);
      }),
      switchMap((response) => {
        return this.me.getMe().pipe(
          map((me): LoginResult => {
            return {
              accessToken: response.token,
              user: this.mapMeToCurrentUser(me),
            };
          }),
        );
      }),
      tap((result) => {
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

  private mapMeToCurrentUser(me: Me): CurrentUser {
    return {
      id: me.id,
      email: me.email,
      displayName: me.email,
      status: me.status,
      roles: me.roles,
    };
  }
}
