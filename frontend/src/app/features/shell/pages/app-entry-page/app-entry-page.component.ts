import { Component, inject, OnInit } from '@angular/core';
import { Router } from '@angular/router';

/**
 * Resolves the connected application entry point.
 *
 * Temporary V1 behavior: redirect authenticated users to the application
 * home page. Later, this page will resolve organization, club and access
 * context before choosing the right destination.
 */
@Component({
  selector: 'app-app-entry-page',
  templateUrl: './app-entry-page.component.html',
  styleUrl: './app-entry-page.component.scss',
})
export class AppEntryPageComponent implements OnInit {
  private readonly router = inject(Router);

  ngOnInit(): void {
    void this.router.navigate(['/app/home'], {
      replaceUrl: true,
    });
  }
}
