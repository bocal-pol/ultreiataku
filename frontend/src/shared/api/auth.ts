/**
 * Service API — Auth SSO
 * Flow : redirect → callback → fetchMe → store
 * Token Bearer stocké dans localStorage('ultreia_token')
 */

import { apiFetch } from './client.ts';
import type { MeResponseDto } from '../../dtos/pilgrimage.ts';
import { mapCurrentUser } from '../../mappers/pilgrimage.ts';
import type { CurrentUserModel } from '../../models/pilgrimage.ts';

const AUTH_LOGIN_URL = import.meta.env['VITE_AUTH_LOGIN_URL'] as string | undefined
  ?? '/auth/login';

export async function fetchMe(signal?: AbortSignal): Promise<CurrentUserModel> {
  const dto = await apiFetch<MeResponseDto>('/me', { signal });
  return mapCurrentUser(dto);
}

/**
 * Redirige vers le SSO central SiteV26.
 * returnPath : chemin à restaurer après le login (ex. '/trips/join/TOKEN')
 */
export function redirectToLogin(returnPath?: string): void {
  const returnUrl = `${window.location.origin}/auth/callback`;
  const state = returnPath ?? window.location.pathname + window.location.search;
  // Stocker le chemin de retour pour le callback
  sessionStorage.setItem('ultreia_return_path', state);
  const loginUrl = new URL(AUTH_LOGIN_URL);
  loginUrl.searchParams.set('app', 'ultreiataku');
  loginUrl.searchParams.set('return', returnUrl);
  window.location.href = loginUrl.toString();
}

export function logout(): void {
  localStorage.removeItem('ultreia_token');
  localStorage.removeItem('ultreia_user');
  window.location.href = '/belgique';
}
