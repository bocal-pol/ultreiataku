/**
 * Service API — Auth SSO
 *
 * P0-01 (SEC-ULTREIA-AUTH) — Migration Bearer/localStorage → session cookie.
 * Flow : redirect vers SSO central → callback /admin/sso/callback (backend)
 *        → Auth::login() + session()->regenerate() → cookie HttpOnly posé
 *        → frontend appelle /api/pilgrimage/me avec credentials: 'include'
 *
 * P1-05 résolu : aucun token stocké en localStorage.
 * La session est un cookie HttpOnly géré par le navigateur — inaccessible à JS.
 */

import { apiFetch } from './client.ts';
import type { MeResponseDto } from '../../dtos/pilgrimage.ts';
import { mapCurrentUser } from '../../mappers/pilgrimage.ts';
import type { CurrentUserModel } from '../../models/pilgrimage.ts';

// URL absolue de l'UI de login du SSO central (Auth). Fallback sur l'origine
// courante + /login pour rester une URL ABSOLUE (new URL() jette sur un chemin
// relatif — cause d'un bouton "se connecter" muet si la variable est absente).
const AUTH_LOGIN_URL = (import.meta.env['VITE_AUTH_LOGIN_URL'] as string | undefined)
  ?? `${window.location.origin}/login`;

export async function fetchMe(signal?: AbortSignal): Promise<CurrentUserModel> {
  const dto = await apiFetch<MeResponseDto>('/me', { signal });
  return mapCurrentUser(dto);
}

/**
 * Redirige vers le SSO central SiteV26.
 * returnPath : chemin à restaurer après le login (ex. '/trips/join/TOKEN')
 */
export function redirectToLogin(returnPath?: string): void {
  // Le callback SSO Filament pose le cookie de session — le frontend
  // n'a pas besoin de recevoir de token, il récupère sa session via cookie.
  const returnUrl = `${window.location.origin}/callback`;
  const state = returnPath ?? window.location.pathname + window.location.search;
  sessionStorage.setItem('ultreia_return_path', state);
  const loginUrl = new URL(AUTH_LOGIN_URL);
  loginUrl.searchParams.set('app', 'ultreiataku');
  loginUrl.searchParams.set('return', returnUrl);
  window.location.href = loginUrl.toString();
}

/**
 * Déconnecte le pèlerin :
 * 1. Appelle POST /api/pilgrimage/logout pour invalider la session côté serveur.
 * 2. Redirige vers / (SplashScreen) quelle que soit l'issue du POST.
 *
 * L'erreur réseau est silencieuse — si le serveur est injoignable ou si la session
 * est déjà expirée, on redirige quand même vers l'accueil.
 */
export async function logout(): Promise<void> {
  try {
    await fetch('/api/pilgrimage/logout', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    });
  } catch {
    // Session serveur déjà expirée ou réseau absent — continuer la déconnexion locale.
  }
  window.location.href = '/';
}
