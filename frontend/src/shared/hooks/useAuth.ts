/**
 * Hook TanStack Query — session courante
 * Séparé du AuthContext pour les composants qui veulent
 * juste lire le user sans le contexte complet.
 */

import { useQuery } from '@tanstack/react-query';
import { fetchMe } from '../api/auth.ts';
import type { CurrentUserModel } from '../../models/pilgrimage.ts';

export const AUTH_QUERY_KEY = ['me'] as const;

export function useCurrentUser() {
  const hasToken = localStorage.getItem('ultreia_token') !== null;

  return useQuery<CurrentUserModel, Error>({
    queryKey: AUTH_QUERY_KEY,
    queryFn: ({ signal }) => fetchMe(signal),
    enabled: hasToken,
    staleTime: Infinity,
    retry: false,
  });
}
