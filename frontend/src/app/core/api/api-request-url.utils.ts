/**
 * Returns true when the URL targets the configured Ramalo API base path.
 */
export function isApiRequest(url: string, apiBaseUrl: string): boolean {
  const normalizedApiBaseUrl = normalizePath(apiBaseUrl);

  if (url.startsWith('http://') || url.startsWith('https://')) {
    try {
      const parsedUrl = new URL(url);

      return normalizePath(parsedUrl.pathname).startsWith(normalizedApiBaseUrl);
    } catch {
      return false;
    }
  }

  return normalizePath(url).startsWith(normalizedApiBaseUrl);
}

/**
 * Extracts the path part from an absolute or relative request URL.
 */
export function extractRequestPath(url: string): string {
  if (url.startsWith('http://') || url.startsWith('https://')) {
    try {
      return normalizePath(new URL(url).pathname);
    } catch {
      return normalizePath(url);
    }
  }

  const queryIndex = url.indexOf('?');
  const path = queryIndex === -1 ? url : url.slice(0, queryIndex);

  return normalizePath(path);
}

function normalizePath(path: string): string {
  const trimmed = path.trim();

  if (trimmed === '') {
    return '/';
  }

  const withLeadingSlash = trimmed.startsWith('/') ? trimmed : `/${trimmed}`;

  return withLeadingSlash.endsWith('/') && withLeadingSlash !== '/'
    ? withLeadingSlash.slice(0, -1)
    : withLeadingSlash;
}
