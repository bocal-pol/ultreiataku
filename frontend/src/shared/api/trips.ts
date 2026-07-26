/**
 * Service API — Trips
 * Reçoit les DTOs, mappe vers Models UI.
 * Les composants ne voient jamais les DTOs.
 */

import { apiFetch } from './client.ts';
import type {
  TripResponseDto,
  TripCreateRequestDto,
  DepartureCreateRequestDto,
  DepartureResponseDto,
  OccupancyResponseDto,
  InviteTokenResponseDto,
  TripJoinPreviewResponseDto,
  ApiListResponseDto,
} from '../../dtos/pilgrimage.ts';
import {
  mapTrip,
  mapDeparture,
  mapOccupancy,
  mapTripJoinPreview,
} from '../../mappers/pilgrimage.ts';
import type {
  TripModel,
  DepartureModel,
  OccupancyModel,
  TripJoinPreviewModel,
} from '../../models/pilgrimage.ts';

export async function fetchMyTrips(signal?: AbortSignal): Promise<TripModel[]> {
  const resp = await apiFetch<ApiListResponseDto<TripResponseDto>>('/trips', { signal });
  return resp.data.map(mapTrip);
}

export async function fetchTrip(id: string, signal?: AbortSignal): Promise<TripModel> {
  const resp = await apiFetch<{ data: TripResponseDto }>(`/trips/${encodeURIComponent(id)}`, { signal });
  return mapTrip(resp.data);
}

export async function createTrip(dto: TripCreateRequestDto): Promise<TripModel> {
  const resp = await apiFetch<{ data: TripResponseDto }>('/trips', {
    method: 'POST',
    body: JSON.stringify(dto),
  });
  return mapTrip(resp.data);
}

export async function addDeparture(
  tripId: string,
  dto: DepartureCreateRequestDto,
): Promise<DepartureModel> {
  const resp = await apiFetch<{ data: DepartureResponseDto }>(
    `/trips/${encodeURIComponent(tripId)}/departures`,
    { method: 'POST', body: JSON.stringify(dto) },
  );
  return mapDeparture(resp.data);
}

export async function fetchOccupancy(
  tripId: string,
  signal?: AbortSignal,
): Promise<OccupancyModel[]> {
  const resp = await apiFetch<{ data: OccupancyResponseDto[] }>(
    `/trips/${encodeURIComponent(tripId)}/occupancy`,
    { signal },
  );
  return resp.data.map(mapOccupancy);
}

export async function generateInviteToken(tripId: string): Promise<string> {
  const resp = await apiFetch<InviteTokenResponseDto>(
    `/trips/${encodeURIComponent(tripId)}/invite-token`,
    { method: 'POST', body: JSON.stringify({}) },
  );
  return resp.token;
}

export async function revokeInviteToken(tripId: string): Promise<void> {
  await apiFetch<void>(`/trips/${encodeURIComponent(tripId)}/invite-token`, {
    method: 'DELETE',
  });
}

export async function joinByToken(token: string): Promise<TripModel> {
  const resp = await apiFetch<{ data: TripResponseDto }>(
    `/trips/join/${encodeURIComponent(token)}`,
    { method: 'POST', body: JSON.stringify({}) },
  );
  return mapTrip(resp.data);
}

export async function fetchTripJoinPreview(
  token: string,
  signal?: AbortSignal,
): Promise<TripJoinPreviewModel> {
  const resp = await apiFetch<TripJoinPreviewResponseDto>(
    `/trips/join-preview/${encodeURIComponent(token)}`,
    { signal },
  );
  return mapTripJoinPreview(resp);
}
