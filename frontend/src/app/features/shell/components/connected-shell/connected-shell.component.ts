import {ChangeDetectionStrategy, Component} from '@angular/core';
import {RouterOutlet} from '@angular/router';
import {AppHeaderComponent} from '../app-header/app-header.component';
import {DesktopSidebarComponent} from '../desktop-sidebar/desktop-sidebar.component';
import {MobileDrawerComponent} from '../mobile-drawer/mobile-drawer.component';

@Component({
  selector: 'app-connected-shell',
  imports: [RouterOutlet, AppHeaderComponent, DesktopSidebarComponent, MobileDrawerComponent,],
  templateUrl: './connected-shell.component.html',
  styleUrl: './connected-shell.component.scss',
})
export class ConnectedShellComponent {
}
