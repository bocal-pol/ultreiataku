import { useQuery } from '@tanstack/react-query';
import { fetchStages, fetchStageDetail } from '../api/stages.ts';
import { getCachedStage, cacheStage } from '../db/indexeddb.ts';
import type { StageModel, StageDetailModel } from '../../models/pilgrimage.ts';

export function useStages(country: string) {
  return useQuery<StageModel[], Error>({
    queryKey: ['stages', country],
    queryFn: ({ signal }) => fetchStages(country, signal),
    staleTime: 5 * 60 * 1000,
    retry: (failureCount, error) => {
      if ('status' in error && (error as { status: number }).status === 404) return false;
      return failureCount < 2;
    },
  });
}

export function useStageDetail(code: string) {
  return useQuery<StageDetailModel, Error>({
    queryKey: ['stage', code],
    queryFn: async ({ signal }) => {
      // Stale-while-revalidate : essai cache IDB d'abord
      const cached = await getCachedStage(code);
      if (cached) {
        // Revalidation en arrière-plan (effet de bord acceptable ici)
        void fetchStageDetail(code, signal).then(fresh => cacheStage(fresh)).catch(() => null);
        return cached;
      }
      const fresh = await fetchStageDetail(code, signal);
      await cacheStage(fresh);
      return fresh;
    },
    staleTime: 5 * 60 * 1000,
  });
}
