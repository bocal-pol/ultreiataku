/**
 * Service API — GPX Traces
 * GET /gpx/{id}/simplified → GeoJSON → GpxLineModel
 */

import { apiFetch } from './client.ts';
import type { GeoJsonCollectionDto } from '../../dtos/pilgrimage.ts';
import { mapGeoJson } from '../../mappers/pilgrimage.ts';
import type { GpxLineModel } from '../../models/pilgrimage.ts';

export async function fetchGpxSimplified(gpxId: string, signal?: AbortSignal): Promise<GpxLineModel> {
  const dto = await apiFetch<GeoJsonCollectionDto>(
    `/gpx/${encodeURIComponent(gpxId)}/simplified`,
    { signal },
  );
  return mapGeoJson(dto);
}
