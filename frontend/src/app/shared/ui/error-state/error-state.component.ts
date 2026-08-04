import { Component, input, output } from '@angular/core';

/**
 * Displays a reusable page-level error message.
 */
@Component({
  selector: 'app-error-state',
  templateUrl: './error-state.component.html',
  styleUrl: './error-state.component.scss',
})
export class ErrorStateComponent {
  readonly title = input('Une erreur est survenue');
  readonly message = input.required<string>();
  readonly actionLabel = input<string | null>(null);

  readonly action = output<void>();

  protected onAction(): void {
    this.action.emit();
  }
}
