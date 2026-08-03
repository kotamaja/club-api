import { Service, signal } from '@angular/core';

/**
 * Stores the current JWT access token and refresh token.
 *
 * The current implementation keeps both tokens in memory. A later backend
 * evolution may move the refresh token to an httpOnly cookie.
 */
@Service()
export class TokenService {
  private readonly accessToken = signal<string | null>(null);
  private readonly refreshToken = signal<string | null>(null);

  getAccessToken(): string | null {
    return this.accessToken();
  }

  setAccessToken(token: string): void {
    this.accessToken.set(token);
  }

  getRefreshToken(): string | null {
    return this.refreshToken();
  }

  setRefreshToken(token: string): void {
    this.refreshToken.set(token);
  }

  clearTokens(): void {
    this.accessToken.set(null);
    this.refreshToken.set(null);
  }
}
