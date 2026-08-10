import { Component, inject } from '@angular/core';
import { RouterOutlet } from '@angular/router';

import { RouteLoadingService } from './core/loading/route-loading.service';
import { TopLoaderComponent } from './layout/top-loader/top-loader.component';

/**
 * Root application component.
 */
@Component({
  selector: 'app-root',
  imports: [
    RouterOutlet,
    TopLoaderComponent,
  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss',
})
export class AppComponent {
  private readonly routeLoadingService = inject(RouteLoadingService);

  constructor() {
    this.routeLoadingService.start();
  }
}
