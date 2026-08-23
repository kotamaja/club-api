import { inject, Service } from '@angular/core';
import { catchError, map, Observable, of, switchMap, tap } from 'rxjs';
import { ApiClientService } from '../../api/api-client.service';
import { CurrentContextService } from '../../context/current/current-context.service';
import { LoginResponse, PasswordLoginCommand, RefreshResponse } from '../api/auth-api.model';
import { CurrentUser } from '../current-user/current-user.model';
import { LoginResult } from '../session/login-result.model';
import { Me } from '../current-user/me.model';
import { MeService } from '../current-user/me.service';
import { SessionService } from '../session/session.service';
import { TokenService } from '../session/token.service';
import { ContextStorageService } from '../../context/storage/context-storage.service';
import { ContextResolverService } from '../../context/resolution/context-resolver.service';
import {OrganizationAccessStoreService} from '../../context/organization-access/organization-access-store.service';
import {ClubAccessStoreService} from '../../context/club-access/club-access-store.service';

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
  private readonly contextStorage = inject(ContextStorageService);
  private readonly contextResolver = inject(ContextResolverService);
  private readonly organizationAccessStore = inject(OrganizationAccessStoreService);
  private readonly clubAccessStore = inject(ClubAccessStoreService);

  loginWithPassword(email: string, password: string): Observable<LoginResult> {
    this.currentContext.reset();
    this.organizationAccessStore.clear();
    this.clubAccessStore.clear();
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
      }),
      switchMap((result) => {
        return this.contextResolver.resolve().pipe(
          map(() => result),
        );
      }),
    );
  }

  restoreSession(): Observable<boolean> {
    return this.api.post<RefreshResponse, Record<string, never>>('/auth/refresh', {}, {
      withCredentials: true,
    }).pipe(
      tap((response) => {
        this.token.setAccessToken(response.token);
      }),
      switchMap(() => {
        return this.me.getMe();
      }),
      tap((me) => {
        this.session.setAuthenticated(this.mapMeToCurrentUser(me));
      }),
      switchMap(() => {
        return this.contextResolver.resolve();
      }),
      map(() => true),
      catchError(() => {
        this.token.clearAccessToken();
        this.session.setAnonymous();
        this.currentContext.reset();
        this.contextStorage.clear();
        this.organizationAccessStore.clear();
        this.clubAccessStore.clear();

        return of(false);
      }),
    );
  }

  requestEmailCode(email: string): Observable<void> {
    // Temporary placeholder until the passwordless API exists.
    void email;

    return of(void 0);
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
    this.contextStorage.clear();
    this.organizationAccessStore.clear();
    this.clubAccessStore.clear();

    return this.api.post<void, Record<string, never>>('/auth/logout', {}, {
      withCredentials: true,
    }).pipe(
      catchError(() => {
        return of(void 0);
      }),
    );
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
