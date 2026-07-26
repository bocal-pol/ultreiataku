/**
 * Client HTTP — fetch + retry + Bearer token
 * Le token est lu depuis localStorage (posé par le SSO callback).
 * 401 global : clear token + redirect vers /auth/login
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
