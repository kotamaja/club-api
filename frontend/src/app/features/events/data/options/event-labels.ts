/**
 * Human-readable labels for event types.
 */
export const eventTypeLabels = {
  general: 'Général',
  course: 'Cours',
  training: 'Entraînement',
  trip: 'Sortie',
  meeting: 'Réunion',
  competition: 'Compétition',
} as const;

/**
 * Human-readable labels for event statuses.
 */
export const eventStatusLabels = {
  draft: 'Brouillon',
  published: 'Publié',
  cancelled: 'Annulé',
  archived: 'Archivé',
} as const;
