/**
 * DTOs — Journal de voyage API — Vague 1e
 * Miroir exact des Laravel API Resources.
 * Ne jamais utiliser ces types dans les composants ou le store.
 */

import type { PilgrimResponseDto, StageResponseDto } from './pilgrimage.ts';

export type JournalVisibility = 'private' | 'members' | 'public';

export type JournalMood = 'great' | 'good' | 'neutral' | 'tired' | 'difficult';

export interface JournalPhotoResponseDto {
  id: string;
  entry_id: string;
  alt_text: string | null;
  keep_location: boolean;
  sort_order: number | null;
  created_at: string;
}

export interface JournalEntryResponseDto {
  id: string;
  trip_id: string;
  pilgrim_id: string | null;
  stage_id: string | null;
  title: string | null;
  body: string | null;
  entry_date: string;
  latitude: number | null;
  longitude: number | null;
  visibility: JournalVisibility;
  mood: JournalMood | null;
  km_walked_today: number | null;
  is_synced: boolean;
  local_id: string | null;
  photos_count: number | null;
  photos?: JournalPhotoResponseDto[];
  pilgrim: PilgrimResponseDto;
  stage?: StageResponseDto | null;
  created_at: string;
  updated_at: string;
}

export interface JournalEntryCreateRequestDto {
  trip_id: string;
  stage_id?: string | null;
  title?: string | null;
  body?: string | null;
  entry_date: string;
  visibility?: JournalVisibility;
  mood?: JournalMood | null;
  km_walked_today?: number | null;
  local_id: string;
  updated_at_client: string;
}

export interface JournalEntrySyncResponseDto {
  id: string;
  local_id: string;
  synced_at: string;
}

export interface JournalEntriesListResponseDto {
  data: JournalEntryResponseDto[];
  meta: {
    has_more: boolean;
    next_cursor: string | null;
  };
}
