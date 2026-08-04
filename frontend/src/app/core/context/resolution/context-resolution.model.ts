/**
 * Result returned after trying to resolve the current frontend context.
 */
export type ContextResolution =
  | { status: 'resolved' }
  | { status: 'needsSelection' }
  | { status: 'noAccess' };
