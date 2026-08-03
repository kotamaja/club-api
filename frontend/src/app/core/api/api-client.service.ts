import {HttpClient, HttpParams} from '@angular/common/http';
import {inject, Service} from '@angular/core';
import {Observable} from 'rxjs';

import {ApiCollection} from './api-collection.model';
import {ApiPage} from './api-page.model';
import {ApiQueryParams} from './api-query.model';
import {ApiUrlService} from './api-url.service';

/**
 * Small wrapper around HttpClient for Ramalo API calls.
 *
 * It centralizes URL construction and query parameter serialization while
 * keeping feature services explicit and simple.
 */
@Service()
export class ApiClientService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = inject(ApiUrlService);

  get<TResponse>(path: string, options?: ApiRequestOptions): Observable<TResponse> {
    return this.http.get<TResponse>(this.apiUrl.build(path), {
      params: this.toHttpParams(options?.params),
      withCredentials: options?.withCredentials,
    });
  }

  getPage<TItem>(path: string, options?: ApiRequestOptions): Observable<ApiPage<TItem>> {
    return this.get<ApiPage<TItem>>(path, {
      ...options,
      params: {
        ...options?.params,
        pagination: true,
      },
    });
  }

  getCollection<TItem>(path: string, options?: ApiRequestOptions): Observable<ApiCollection<TItem>> {
    return this.get<ApiCollection<TItem>>(path, {
      ...options,
      params: {
        ...options?.params,
        pagination: false,
      },
    });
  }

  post<TResponse, TBody = unknown>(path: string, body: TBody, options?: ApiRequestOptions): Observable<TResponse> {
    return this.http.post<TResponse>(this.apiUrl.build(path), body, {
      withCredentials: options?.withCredentials,
    });
  }

  patch<TResponse, TBody = unknown>(path: string, body: TBody, options?: ApiRequestOptions): Observable<TResponse> {
    return this.http.patch<TResponse>(this.apiUrl.build(path), body, {
      withCredentials: options?.withCredentials,
    });
  }

  delete<TResponse = void>(path: string, options?: ApiRequestOptions): Observable<TResponse> {
    return this.http.delete<TResponse>(this.apiUrl.build(path), {
      withCredentials: options?.withCredentials,
    });
  }

  private toHttpParams(params?: ApiQueryParams): HttpParams | undefined {
    if (!params) {
      return undefined;
    }

    let httpParams = new HttpParams();

    for (const [key, value] of Object.entries(params)) {
      if (value === null || value === undefined) {
        continue;
      }

      if (Array.isArray(value)) {
        for (const item of value) {
          httpParams = httpParams.append(key, String(item));
        }

        continue;
      }

      httpParams = httpParams.set(key, String(value));
    }

    return httpParams;
  }
}

/**
 * Common options accepted by ApiClientService requests.
 */
export type ApiRequestOptions = {
  params?: ApiQueryParams;
  withCredentials?: boolean;
};
