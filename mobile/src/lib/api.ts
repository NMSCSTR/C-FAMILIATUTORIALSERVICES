import axios from 'axios';

import { API_BASE_URL, API_QUERY_ROUTES } from './config';

export const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
  headers: { Accept: 'application/json' },
});

type TokenGetter = () => Promise<string | null>;
type OnUnauthorized = () => void;

let tokenGetter: TokenGetter = async () => null;
let onUnauthorized: OnUnauthorized = () => {};

export function bindAuthHooks(get: TokenGetter, unauthorized: OnUnauthorized): void {
  tokenGetter = get;
  onUnauthorized = unauthorized;
}

api.interceptors.request.use((config) => {
  if (API_QUERY_ROUTES && config.url) {
    config.params = { ...(config.params ?? {}), route: String(config.url) };
    config.url = '/';
  }
  return config;
});

api.interceptors.request.use(async (config) => {
  const token = await tokenGetter();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      onUnauthorized();
    }
    return Promise.reject(error);
  }
);

type ApiErrorShape = {
  error?: { code?: string; message?: string; fields?: Record<string, string> };
};

export function apiErrorMessage(error: unknown, fallback = 'Something went wrong. Please try again.'): string {
  if (axios.isAxiosError<ApiErrorShape>(error)) {
    return error.response?.data?.error?.message ?? error.message ?? fallback;
  }
  return fallback;
}

export function apiFieldErrors(error: unknown): Record<string, string> {
  if (axios.isAxiosError<ApiErrorShape>(error)) {
    return error.response?.data?.error?.fields ?? {};
  }
  return {};
}
