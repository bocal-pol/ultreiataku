import { useQuery } from '@tanstack/react-query';
import { fetchGpxSimplified } from '../api/gpx.ts';
import { getCachedGpx, cacheGpx } from '../db/indexeddb.ts';
import type { GpxLineModel } from '../../models/pilgrimage.ts';

export function useGpxSimplified(gpxId: string | null) {
  return useQuery<GpxLineModel, Error>({
    queryKey: ['gpx', gpxId],
    enabled: gpxId !== null,
    queryFn: async ({ signal }) => {
      if (!gpxId) return { coordinates: [] };
      const cached = await getCachedGpx(gpxId);
      if (cached) {
        void fetchGpxSimplified(gpxId, signal).then(f => cacheGpx(gpxId, f)).catch(() => null);
        return cached;
      }
      const fresh = await fetchGpxSimplified(gpxId, signal);
      await cacheGpx(gpxId, fresh);
      return fresh;
    },
    staleTime: 7 * 24 * 60 * 60 * 1000,
  });
}
