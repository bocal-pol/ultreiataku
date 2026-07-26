/**
 * Hooks TanStack Query — Pack (Sac)
 */

import { useQuery } from '@tanstack/react-query';
import { fetchPackScenarios, fetchPackScenario } from '../api/pack.ts';
import type { PackScenarioModel } from '../../models/pack.ts';

// ─── Query keys ──────────────────────────────────────────────────────────────

export const packKeys = {
  all: ['pack'] as const,
  scenariosByPilgrim: (pilgrimId: string) =>
    [...packKeys.all, 'scenarios', 'pilgrim', pilgrimId] as const,
  scenarioDetail: (id: string) => [...packKeys.all, 'scenario', id] as const,
};

// ─── Queries ─────────────────────────────────────────────────────────────────

export function usePackScenarios(pilgrimId: string) {
  return useQuery<PackScenarioModel[], Error>({
    queryKey: packKeys.scenariosByPilgrim(pilgrimId),
    queryFn: ({ signal }) => fetchPackScenarios(pilgrimId, signal),
    enabled: pilgrimId !== '',
    staleTime: 5 * 60 * 1000,
    retry: (failureCount, error) => {
      if ('status' in error && (error as { status: number }).status === 401) return false;
      return failureCount < 2;
    },
  });
}

export function usePackScenario(scenarioId: string) {
  return useQuery<PackScenarioModel, Error>({
    queryKey: packKeys.scenarioDetail(scenarioId),
    queryFn: ({ signal }) => fetchPackScenario(scenarioId, signal),
    enabled: scenarioId !== '',
    staleTime: 5 * 60 * 1000,
    retry: (failureCount, error) => {
      if ('status' in error && (error as { status: number }).status === 401) return false;
      return failureCount < 2;
    },
  });
}
