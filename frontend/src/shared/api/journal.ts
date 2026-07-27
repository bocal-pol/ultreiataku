/**
 * Service API — Journal de voyage
 * Reçoit les DTOs, mappe vers Models UI.
 * Les composants ne voient jamais les DTOs.
 *
 * P0-01 (SEC-ULTREIA-AUTH) — Upload photo migré vers credentials: 'include'.
 * Suppression du Bearer token en Authorization header.
 */

import { apiFetch, API_BASE } from './client.ts';
import type {
  JournalEntriesListResponseDto,
  JournalEntryCreateRequestDto,
  JournalEntrySyncResponseDto,
} from '../../dtos/journal.ts';
import { mapJournalEntry } from '../../mappers/journal.ts';
import type { JournalEntryModel } from '../../models/journal.ts';

export interface JournalEntriesPage {
  entries: JournalEntryModel[];
  hasMore: boolean;
  nextCursor: string | null;
}

export async function fetchJournalEntries(
  tripId: string,
  afterId?: string,
  signal?: AbortSignal,
): Promise<JournalEntriesPage> {
  const params = new URLSearchParams({ limit: '20' });
  if (afterId) params.set('after_id', afterId);

  const resp = await apiFetch<JournalEntriesListResponseDto>(
    `/trips/${encodeURIComponent(tripId)}/journal?${params.toString()}`,
    { signal },
  );

  return {
    entries: resp.data.map(mapJournalEntry),
    hasMore: resp.meta.has_more,
    nextCursor: resp.meta.next_cursor,
  };
}

export async function postJournalEntry(
  dto: JournalEntryCreateRequestDto,
): Promise<JournalEntrySyncResponseDto> {
  return apiFetch<JournalEntrySyncResponseDto>('/journal/entries', {
    method: 'POST',
    body: JSON.stringify(dto),
  });
}

export async function uploadJournalPhoto(
  entryId: string,
  file: File,
  altText: string | null,
  keepLocation: boolean,
): Promise<void> {
  const formData = new FormData();
  formData.append('photo', file);
  if (altText) formData.append('alt_text', altText);
  formData.append('keep_location', keepLocation ? '1' : '0');

  const response = await fetch(
    `${API_BASE}/journal/entries/${encodeURIComponent(entryId)}/photos`,
    {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        // Pas de Content-Type : FormData le pose automatiquement avec le boundary.
        // Pas de Authorization : la session est dans le cookie HttpOnly.
      },
      body: formData,
      // Indispensable pour que le cookie de session HttpOnly soit envoyé.
      credentials: 'include',
    },
  );

  if (response.status === 401) {
    window.dispatchEvent(new CustomEvent('ultreia:unauthorized'));
    throw new Error('Unauthorized');
  }

  if (!response.ok) {
    const text = await response.text();
    throw new Error(`Upload failed: ${text}`);
  }
}

/** Construit l'URL proxy pour une photo journal (jamais URL MinIO directe). */
export function buildPhotoUrl(photoId: string): string {
  return `${API_BASE}/journal/photos/${encodeURIComponent(photoId)}`;
}
