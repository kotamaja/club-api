import { Component, input } from '@angular/core';
import { RouterLink } from '@angular/router';

/**
 * Displays a reusable forbidden access state.
 *
 * This component is used when the current user cannot access a page or a
 * specific feature because a required capability is missing.
 */
@Component({
  selector: 'app-forbidden-state',
  imports: [RouterLink],
  templateUrl: './forbidden-state.component.html',
  styleUrl: './forbidden-state.component.scss',
})
export class ForbiddenStateComponent {
  readonly title = input('Accès non autorisé');
  readonly message = input('Vous n’avez pas les droits nécessaires pour accéder à cette page.');
  readonly homeLink = input('/app/home');
}
