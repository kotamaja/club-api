import {Component, computed, effect, inject, signal} from '@angular/core';

import { GlobalLoadingService } from '../../core/loading/global-loading.service';

/**
 * Displays a thin global loading bar at the top of the viewport.
 */
@Component({
  selector: 'app-top-loader',
  templateUrl: './top-loader.component.html',
  styleUrl: './top-loader.component.scss',
})
export class TopLoaderComponent {
  private readonly globalLoading = inject(GlobalLoadingService);

  protected readonly isLoading = this.globalLoading.isLoading;
  protected readonly progress = signal(0);

  private progressTimer: ReturnType<typeof setInterval> | null = null;

  protected readonly isVisible = computed(() => {
    return this.isLoading() || this.progress() > 0;
  });

  constructor() {
    let hideTimer: ReturnType<typeof setTimeout> | null = null;

    effect(() => {
      if (this.isLoading()) {
        if (hideTimer !== null) {
          clearTimeout(hideTimer);
          hideTimer = null;
        }

        this.startProgress();
        return;
      }

      this.completeProgress();

      hideTimer = setTimeout(() => {
        this.progress.set(0);
      }, 200);
    });
  }

  private startProgress(): void {
    if (this.progress() === 0) {
      this.progress.set(15);
    }

    if (this.progressTimer !== null) {
      return;
    }

    this.progressTimer = setInterval(() => {
      this.progress.update((value) => {
        if (value < 70) {
          return value + 8;
        }

        if (value < 90) {
          return value + 2;
        }

        return value;
      });
    }, 150);
  }

  private completeProgress(): void {
    if (this.progressTimer !== null) {
      clearInterval(this.progressTimer);
      this.progressTimer = null;
    }

    if (this.progress() > 0) {
      this.progress.set(100);
    }
  }
}
