import { Service, signal } from '@angular/core';

/**
 * Stores the current access token used by authenticated API requests.
 *
 * The refresh token should remain managed by the backend through an httpOnly
 * cookie and should not be exposed to frontend code.
 */
@Service()
export class TokenService {
  private readonly accessToken = signal<string | null>(null);

  getAccessToken(): string | null {
    return this.accessToken();
  }

  setAccessToken(token: string): void {
    this.accessToken.set(token);
  }

  clearAccessToken(): void {
    this.accessToken.set(null);
  }
}
