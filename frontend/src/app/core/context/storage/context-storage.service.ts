import { Service } from '@angular/core';

import { StoredContextSelection } from './context-storage.model';

/**
 * Persists the last selected organization and club in the browser.
 *
 * The stored values are not trusted as permissions. They are only reused
 * after being validated against the current backend context.
 */
@Service()
export class ContextStorageService {
  private readonly storageKey = 'ramalo.currentContext';

  get(): StoredContextSelection | null {
    const rawValue = localStorage.getItem(this.storageKey);

    if (rawValue === null) {
      return null;
    }

    try {
      const parsedValue: unknown = JSON.parse(rawValue);

      if (!this.isStoredContextSelection(parsedValue)) {
        return null;
      }

      return parsedValue;
    } catch {
      return null;
    }
  }

  save(selection: StoredContextSelection): void {
    localStorage.setItem(this.storageKey, JSON.stringify(selection));
  }

  clear(): void {
    localStorage.removeItem(this.storageKey);
  }

  private isStoredContextSelection(value: unknown): value is StoredContextSelection {
    if (typeof value !== 'object' || value === null) {
      return false;
    }

    if (!('organizationId' in value) || !('clubId' in value)) {
      return false;
    }

    const selection = value as Record<string, unknown>;

    return typeof selection['organizationId'] === 'string'
      && (
        selection['clubId'] === null
        || typeof selection['clubId'] === 'string'
      );
  }
}
