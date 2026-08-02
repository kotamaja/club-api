/**
 * Frontend capability used to control routes, navigation and UI actions.
 *
 * Components should check capabilities instead of checking role names directly.
 */
export type Capability =
  | 'canAccessMemberArea'
  | 'canAccessManagementArea'
  | 'canManageMembers'
  | 'canManageMemberships'
  | 'canManageEvents'
  | 'canManageEventRegistrations'
  | 'canReviewPublicRegistrationRequests'
  | 'canManageClubSettings';
