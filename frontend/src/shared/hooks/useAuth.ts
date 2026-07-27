/**
 * Hook TanStack Query — session courante
 * Séparé du AuthContext pour les composants qui veulent
 * juste lire le user sans le contexte complet.
 *
 * P0-01 (SEC-ULTREIA-AUTH) — Suppression de la condition localStorage.
 * La session est dans un cookie HttpOnly — on appelle toujours fetchMe().
 * 401 = non connecté (gère le cas "pas de session").
 */

import { useQuery } from '@tanstack/react-query';
import { fetchMe } from '../api/auth.ts';
import type { CurrentUserModel } from '../../models/pilgrimage.ts';

export const AUTH_QUERY_KEY = ['me'] as const;

export function useCurrentUser() {
  return useQuery<CurrentUserModel, Error>({
    queryKey: AUTH_QUERY_KEY,
    queryFn: ({ signal }) => fetchMe(signal),
    // Toujours actif — la session est dans le cookie, pas dans localStorage.
    // Si 401, TanStack Query retourne error et data=undefined.
    enabled: true,
    staleTime: Infinity,
    retry: false,
  });
}
