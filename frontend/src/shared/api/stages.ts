/**
 * Service API — Stages & Routes
 * Reçoit les paramètres de filtre, mappe DTO → Model UI, retourne les Models.
 * Les composants ne voient jamais les DTOs.
 */

import { apiFetch } from './client.ts';
import type { ApiListResponseDto, StageResponseDto, StageDetailResponseDto } from '../../dtos/pilgrimage.ts';
import { mapStage, mapStageDetail } from '../../mappers/pilgrimage.ts';
import type { StageModel, StageDetailModel } from '../../models/pilgrimage.ts';

export async function fetchStages(country: string, signal?: AbortSignal): Promise<StageModel[]> {
  const resp = await apiFetch<ApiListResponseDto<StageResponseDto>>(
    `/stages?filter[country]=${encodeURIComponent(country)}`,
    { signal },
  );
  return resp.data.map(mapStage);
}

export async function fetchStageDetail(code: string, signal?: AbortSignal): Promise<StageDetailModel> {
  const resp = await apiFetch<{ data: StageDetailResponseDto }>(
    `/stages/${encodeURIComponent(code)}?include=waypoints,accommodations,meals,gpx_traces`,
    { signal },
  );
  return mapStageDetail(resp.data);
}
