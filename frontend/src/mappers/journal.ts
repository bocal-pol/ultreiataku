/**
 * Mappers — Journal DTO → Model UI
 * Aucun DTO ne franchit cette frontière vers les composants.
 */

import type {
  JournalEntryResponseDto,
  JournalPhotoResponseDto,
} from '../dtos/journal.ts';
import type { JournalEntryModel, JournalPhotoModel } from '../models/journal.ts';
import { mapPilgrim, mapStage } from './pilgrimage.ts';

const PHOTO_PROXY_BASE = '/api/pilgrimage/journal/photos';

export function buildPhotoProxyUrl(photoId: string): string {
  return `${PHOTO_PROXY_BASE}/${encodeURIComponent(photoId)}`;
}

export function mapJournalPhoto(dto: JournalPhotoResponseDto): JournalPhotoModel {
  return {
    id: dto.id,
    entryId: dto.entry_id,
    altText: dto.alt_text,
    keepLocation: dto.keep_location,
    sortOrder: dto.sort_order,
    createdAt: dto.created_at,
    proxyUrl: buildPhotoProxyUrl(dto.id),
  };
}

export function mapJournalEntry(dto: JournalEntryResponseDto): JournalEntryModel {
  return {
    id: dto.id,
    tripId: dto.trip_id,
    pilgrimId: dto.pilgrim_id,
    stageId: dto.stage_id,
    title: dto.title,
    body: dto.body,
    entryDate: dto.entry_date,
    latitude: dto.latitude,
    longitude: dto.longitude,
    visibility: dto.visibility,
    mood: dto.mood,
    kmWalkedToday: dto.km_walked_today,
    isSynced: dto.is_synced,
    localId: dto.local_id,
    photosCount: dto.photos_count ?? 0,
    photos: (dto.photos ?? []).map(mapJournalPhoto),
    pilgrim: mapPilgrim(dto.pilgrim),
    stage: dto.stage ? mapStage(dto.stage) : null,
    createdAt: dto.created_at,
    updatedAt: dto.updated_at,
  };
}
