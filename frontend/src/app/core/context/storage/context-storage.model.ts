/**
 * Last organization and club selected by the user.
 *
 * This value is only a frontend preference. It must always be validated
 * against the organizations returned by the backend.
 */
export type StoredContextSelection = {
  organizationId: string;
  clubId: string | null;
};
