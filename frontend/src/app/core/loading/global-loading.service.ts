import { computed, Service, signal } from '@angular/core';

/**
 * Tracks global asynchronous activity used by the top loader.
 */
@Service()
export class GlobalLoadingService {
  private readonly activeCount = signal(0);

  readonly isLoading = computed(() => this.activeCount() > 0);

  start(): void {
    this.activeCount.update((count) => count + 1);
  }

  stop(): void {
    this.activeCount.update((count) => Math.max(0, count - 1));
  }
}
