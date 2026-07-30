/**
 * useGuides / useGuideDetail — TanStack Query hooks pour les guides du Chemin.
 *
 * Lecture publique — pas d'auth requise.
 * staleTime 10 min : les guides ne changent pas fréquemment.
 */

import { useQuery } from '@tanstack/react-query';
import { fetchGuides, fetchGuideDetail } from '../api/guides.ts';
import type { GuideListItemModel, GuideDetailModel } from '../../models/pilgrimage.ts';

/** Liste complète des guides publiés, groupables par catégorie côté composant. */
export function useGuides() {
  return useQuery<GuideListItemModel[], Error>({
    queryKey: ['guides'],
    queryFn: ({ signal }) => fetchGuides(signal),
    staleTime: 10 * 60 * 1000,
  });
}

/** Détail d'un guide par slug (contenu markdown + métadonnées). */
export function useGuideDetail(slug: string) {
  return useQuery<GuideDetailModel, Error>({
    queryKey: ['guide', slug],
    queryFn: ({ signal }) => fetchGuideDetail(slug, signal),
    staleTime: 10 * 60 * 1000,
    enabled: slug.length > 0,
  });
}
