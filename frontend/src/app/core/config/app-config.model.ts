/**
 * Runtime configuration used by the Angular application.
 *
 * The API version is centralized so feature services never hardcode /api/v1.
 */
export type AppConfig = {
  apiBaseUrl: string;
  apiVersion: 'v1';
};
