import { Service, signal } from '@angular/core';

/**
 * Stores the current JWT access token used by authenticated API requests.
 *
 * The refresh token is managed by the backend through an httpOnly cookie and
 * is intentionally not exposed to frontend code.
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
