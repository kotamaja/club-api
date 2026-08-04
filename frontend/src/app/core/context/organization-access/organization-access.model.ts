/**
 * Person attached to the authenticated user inside an organization.
 */
export type OrganizationAccessPerson = {
  id: string;
  firstname: string;
  lastname: string;
  email: string;
};

/**
 * Organization available to the authenticated user.
 *
 * This model represents one organization context the user may select after
 * login or after session restoration.
 */
export type OrganizationAccess = {
  organizationUserId: string;
  organizationId: string;
  organizationName: string;
  organizationSlug: string;
  roles: string[];
  enabled: boolean;
  person: OrganizationAccessPerson;
};
