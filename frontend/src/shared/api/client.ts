/**
 * Client HTTP — fetch + retry + cookies de session
 *
 * P0-01 (SEC-ULTREIA-AUTH) — Migration Bearer/localStorage → session cookie HttpOnly
 * ─────────────────────────────────────────────────────────────────────────────────
 * Pattern monorepo SiteV26 (source : Oikotaku/frontend/src/lib/api/client.ts) :
 *   - credentials: 'include' — le navigateur envoie le cookie de session HttpOnly
 *     à chaque requête cross-origin vers le backend.
 *   - Aucun Bearer token. Aucun stockage localStorage de credentials.
 *   - Le cookie est posé par SsoCallbackController (Auth::login + session()->regenerate())
 *     après validation du code SSO central.
 *
 * P1-05 résolu par construction : plus de token en localStorage,
 *   la surface d'attaque XSS sur les credentials est nulle.
 *
 * Gestion 401 : dispatch événement custom 'ultreia:unauthorized'
 *   → AuthProvider redirige vers le SSO central.
 */

const API_BASE = '/api/pilgrimage';

interface FetchOptions {
  method?: string;
  body?: BodyInit;
  signal?: AbortSignal;
}

async function apiFetch<T>(path: string, opts: FetchOptions = {}): Promise<T> {
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  };

  const response = await fetch(`${API_BASE}${path}`, {
    method: opts.method ?? 'GET',
    headers,
    body: opts.body,
    signal: opts.signal,
    // Indispensable pour que le cookie de session HttpOnly soit envoyé
    // avec chaque requête cross-origin (frontend SPA → backend Laravel).
    credentials: 'include',
  });

  if (response.status === 401) {
    // Session expirée ou absente — déclencher un événement custom
    // pour que AuthProvider réagisse et redirige vers le SSO.
    window.dispatchEvent(new CustomEvent('ultreia:unauthorized'));
    throw new ApiError(401, 'Unauthorized');
  }

  if (!response.ok) {
    const text = await response.text();
    throw new ApiError(response.status, text);
  }

  return response.json() as Promise<T>;
}

export class ApiError extends Error {
  status: number;
  constructor(status: number, message: string) {
    super(message);
    this.status = status;
    this.name = 'ApiError';
  }
}

export { apiFetch, API_BASE };
