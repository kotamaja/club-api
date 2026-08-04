import { Component, inject, OnInit } from '@angular/core';
import { Router } from '@angular/router';

import { CurrentContextService } from '../../../../core/context/current/current-context.service';

/**
 * Resolves the connected application entry point.
 *
 * The current context is normally resolved during login or session restoration.
 * This page redirects the user to the correct connected destination.
 */
@Component({
  selector: 'app-app-entry-page',
  templateUrl: './app-entry-page.component.html',
  styleUrl: './app-entry-page.component.scss',
})
export class AppEntryPageComponent implements OnInit {
  private readonly currentContext = inject(CurrentContextService);
  private readonly router = inject(Router);

  ngOnInit(): void {
    const state = this.currentContext.state();

    if (state.status === 'resolved') {
      void this.router.navigate(['/app/home'], {
        replaceUrl: true,
      });

      return;
    }

    void this.router.navigate(['/app/select-organization'], {
      replaceUrl: true,
    });
  }
}
