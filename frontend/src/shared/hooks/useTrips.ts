/**
 * Hooks TanStack Query — Trips
 */

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  fetchMyTrips,
  fetchTrip,
  createTrip,
  addDeparture,
  fetchOccupancy,
  generateInviteToken,
  revokeInviteToken,
  joinByToken,
  fetchTripJoinPreview,
} from '../api/trips.ts';
import type {
  TripModel,
  DepartureModel,
  OccupancyModel,
  TripJoinPreviewModel,
} from '../../models/pilgrimage.ts';
import type {
  TripCreateRequestDto,
  DepartureCreateRequestDto,
} from '../../dtos/pilgrimage.ts';

// ─── Query keys ─────────────────────────────────────────────────────────────

export const tripKeys = {
  all: ['trips'] as const,
  myList: () => [...tripKeys.all, 'my-list'] as const,
  detail: (id: string) => [...tripKeys.all, 'detail', id] as const,
  occupancy: (id: string) => [...tripKeys.all, 'occupancy', id] as const,
  joinPreview: (token: string) => [...tripKeys.all, 'join-preview', token] as const,
};

// ─── Options partielles pour useOccupancy ────────────────────────────────────

interface OccupancyOptions {
  /** Si false, la query ne se déclenche pas */
  enabled?: boolean;
  /** Callback silencieux en cas d'erreur */
  onError?: (err: Error) => void;
}

// ─── Queries ─────────────────────────────────────────────────────────────────

export function useMyTrips() {
  return useQuery<TripModel[], Error>({
    queryKey: tripKeys.myList(),
    queryFn: ({ signal }) => fetchMyTrips(signal),
    staleTime: 2 * 60 * 1000,
    retry: (failureCount, error) => {
      if ('status' in error && (error as { status: number }).status === 401) return false;
      return failureCount < 2;
    },
  });
}

export function useTripDetail(id: string) {
  return useQuery<TripModel, Error>({
    queryKey: tripKeys.detail(id),
    queryFn: ({ signal }) => fetchTrip(id, signal),
    enabled: id !== '',
    staleTime: 60 * 1000,
    retry: (failureCount, error) => {
      if ('status' in error && (error as { status: number }).status === 401) return false;
      return failureCount < 2;
    },
  });
}

export function useOccupancy(
  tripId: string,
  accommodationId?: string,
  options?: OccupancyOptions,
) {
  const isEnabled = options?.enabled !== undefined
    ? options.enabled && tripId !== ''
    : tripId !== '';

  return useQuery<OccupancyModel[], Error>({
    queryKey: tripKeys.occupancy(tripId),
    queryFn: ({ signal }) => fetchOccupancy(tripId, signal),
    enabled: isEnabled,
    staleTime: 5 * 60 * 1000,
    retry: false,
    select: accommodationId
      ? (data) => data.filter(o => o.accommodationId === accommodationId)
      : undefined,
  });
}

export function useTripJoinPreview(token: string) {
  return useQuery<TripJoinPreviewModel, Error>({
    queryKey: tripKeys.joinPreview(token),
    queryFn: ({ signal }) => fetchTripJoinPreview(token, signal),
    enabled: token !== '',
    staleTime: 60 * 1000,
    retry: false,
  });
}

// ─── Mutations ───────────────────────────────────────────────────────────────

export function useCreateTrip() {
  const queryClient = useQueryClient();
  return useMutation<TripModel, Error, TripCreateRequestDto>({
    mutationFn: createTrip,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: tripKeys.myList() });
    },
  });
}

export function useAddDeparture(tripId: string) {
  const queryClient = useQueryClient();
  return useMutation<DepartureModel, Error, DepartureCreateRequestDto>({
    mutationFn: (dto) => addDeparture(tripId, dto),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: tripKeys.detail(tripId) });
    },
  });
}

export function useGenerateInviteToken(tripId: string) {
  const queryClient = useQueryClient();
  return useMutation<string, Error, void>({
    mutationFn: () => generateInviteToken(tripId),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: tripKeys.detail(tripId) });
    },
  });
}

export function useRevokeInviteToken(tripId: string) {
  const queryClient = useQueryClient();
  return useMutation<void, Error, void>({
    mutationFn: () => revokeInviteToken(tripId),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: tripKeys.detail(tripId) });
    },
  });
}

/**
 * Rejoint un voyage via un token d'invitation.
 * Paramètre : { token: string }
 */
export function useJoinByToken() {
  const queryClient = useQueryClient();
  return useMutation<TripModel, Error, { token: string }>({
    mutationFn: ({ token }) => joinByToken(token),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: tripKeys.myList() });
    },
  });
}
