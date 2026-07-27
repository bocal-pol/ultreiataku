/**
 * Client HTTP — fetch + retry + Bearer token
 *
 * Architecture Bearer / localStorage — risque accepté (P1-05)
 * ─────────────────────────────────────────────────────────────
 * Le token SSO est stocké dans localStorage (clé `ultreia_token`).
 *
 * Risque : un script XSS pourrait lire ce token. L'alternative classique
 * est un HttpOnly cookie, mais elle est incompatible avec le mode PWA
 * offline-first (les Service Workers ne voient pas les cookies HttpOnly,
 * et le flow SSO cross-origin multiapp impose une redirection explicite
 * avec le token en query param → localStorage est le seul stockage accessible
 * au moment du callback /auth/callback).
 *
 * Mesures de mitigation compensatoires :
 *   1. CSP stricte : script-src 'self' uniquement (cf. infra + headers Laravel)
 *   2. Purge immédiate sur 401 : localStorage.removeItem('ultreia_token') + ultreia:unauthorized
 *   3. Token à durée de vie courte côté Auth central (exp configurable, défaut 1h)
 *   4. HTTPS obligatoire en production (TLS 1.2+)
 *   5. Aucune donnée PII dans le token (sub = UUID, pas d'email ni de rôle en clair)
 *
 * Évolution future : si Auth central supporte SameSite=Strict HttpOnly cookies,
 * migrer vers credentials: 'include' + suppression du localStorage. Tracker : AUTH-COOKIE-P2.
 */

const API_BASE = '/api/pilgrimage';

function getBearer(): string | null {
  return localStorage.getItem('ultreia_token');
}

interface FetchOptions {
  method?: string;
  body?: BodyInit;
  signal?: AbortSignal;
}

async function apiFetch<T>(path: string, opts: FetchOptions = {}): Promise<T> {
  const token = getBearer();
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };

  const response = await fetch(`${API_BASE}${path}`, {
    method: opts.method ?? 'GET',
    headers,
    body: opts.body,
    signal: opts.signal,
  });

  if (response.status === 401) {
    // Token expiré ou invalide — nettoyage et redirect login
    localStorage.removeItem('ultreia_token');
    localStorage.removeItem('ultreia_user');
    // Déclencher un événement custom pour que AuthProvider réagisse
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
