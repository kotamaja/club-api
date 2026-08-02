import { Component, input } from '@angular/core';

/**
 * Displays a consistent page title and optional description.
 *
 * This component is used by pages to keep headings visually and structurally
 * consistent across the application.
 */
@Component({
  selector: 'app-page-header',
  templateUrl: './page-header.component.html',
  styleUrl: './page-header.component.scss',
})
export class PageHeaderComponent {
  readonly title = input.required<string>();
  readonly description = input<string | null>(null);
}
