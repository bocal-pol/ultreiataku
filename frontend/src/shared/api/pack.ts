/**
 * Service API — Pack (Sac)
 * Reçoit les DTOs, mappe vers Models UI.
 * Les composants ne voient jamais les DTOs.
 */

import { apiFetch } from './client.ts';
import type {
  PackScenariosListResponseDto,
  PackScenarioDetailResponseDto,
} from '../../dtos/pack.ts';
import { mapPackScenario } from '../../mappers/pack.ts';
import type { PackScenarioModel } from '../../models/pack.ts';

export async function fetchPackScenarios(
  pilgrimId: string,
  signal?: AbortSignal,
): Promise<PackScenarioModel[]> {
  const resp = await apiFetch<PackScenariosListResponseDto>(
    `/pilgrims/${encodeURIComponent(pilgrimId)}/pack-scenarios`,
    { signal },
  );
  return resp.data.map(mapPackScenario);
}

export async function fetchPackScenario(
  scenarioId: string,
  signal?: AbortSignal,
): Promise<PackScenarioModel> {
  const resp = await apiFetch<PackScenarioDetailResponseDto>(
    `/pack-scenarios/${encodeURIComponent(scenarioId)}`,
    { signal },
  );
  return mapPackScenario(resp.data);
}
