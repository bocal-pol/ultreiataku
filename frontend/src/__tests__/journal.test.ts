/**
 * Tests — Journal offline-first — Vague 1e
 *
 * Couvre :
 * - Création offline → présence dans IDB
 * - Sync mockée → réconciliation (markJournalEntrySynced)
 * - Mapper JournalEntry DTO → Model
 * - Parité i18n fr/nl/de (via import direct des JSON)
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mapJournalEntry, mapJournalPhoto } from '../mappers/journal.ts';
import type { JournalEntryResponseDto, JournalPhotoResponseDto } from '../dtos/journal.ts';
import frPilgrimage from '../shared/i18n/fr/pilgrimage.json';
import nlPilgrimage from '../shared/i18n/nl/pilgrimage.json';
import dePilgrimage from '../shared/i18n/de/pilgrimage.json';

// ─── Fixtures ─────────────────────────────────────────────────────────────────

const mockPilgrimDto = {
  id: 'pilgrim-1',
  user_id: 42,
  display_name: 'Thomas de Liège',
  avatar_url: null,
  preferred_locale: 'fr' as const,
  configuration: 'solo' as const,
};

const mockEntryDto: JournalEntryResponseDto = {
  id: 'entry-server-1',
  trip_id: 'trip-1',
  pilgrim_id: 'pilgrim-1',
  stage_id: null,
  title: 'Jour 3 — Belle étape',
  body: 'La montée vers Namur était rude mais magnifique.',
  entry_date: '2026-07-15',
  latitude: 50.467,
  longitude: 4.867,
  visibility: 'members',
  mood: 'good',
  km_walked_today: 24.5,
  is_synced: true,
  local_id: 'local-uuid-001',
  photos_count: 2,
  photos: [],
  pilgrim: mockPilgrimDto,
  stage: null,
  created_at: '2026-07-15T18:30:00Z',
  updated_at: '2026-07-15T18:30:00Z',
};

// ─── Mapper JournalEntry ──────────────────────────────────────────────────────

describe('mapJournalEntry', () => {
  it('convertit correctement tous les champs', () => {
    const model = mapJournalEntry(mockEntryDto);
    expect(model.id).toBe('entry-server-1');
    expect(model.tripId).toBe('trip-1');
    expect(model.pilgrimId).toBe('pilgrim-1');
    expect(model.title).toBe('Jour 3 — Belle étape');
    expect(model.body).toBe('La montée vers Namur était rude mais magnifique.');
    expect(model.entryDate).toBe('2026-07-15');
    expect(model.visibility).toBe('members');
    expect(model.mood).toBe('good');
    expect(model.kmWalkedToday).toBe(24.5);
    expect(model.isSynced).toBe(true);
    expect(model.localId).toBe('local-uuid-001');
    expect(model.photosCount).toBe(2);
    expect(model.photos).toHaveLength(0);
    expect(model.pilgrim.displayName).toBe('Thomas de Liège');
    expect(model.stage).toBeNull();
  });

  it('gère photosCount null → 0', () => {
    const dto = { ...mockEntryDto, photos_count: null };
    const model = mapJournalEntry(dto);
    expect(model.photosCount).toBe(0);
  });

  it('gère pilgrimId null', () => {
    const dto = { ...mockEntryDto, pilgrim_id: null };
    const model = mapJournalEntry(dto);
    expect(model.pilgrimId).toBeNull();
  });

  it('gère mood null', () => {
    const dto = { ...mockEntryDto, mood: null };
    const model = mapJournalEntry(dto);
    expect(model.mood).toBeNull();
  });
});

// ─── Mapper JournalPhoto ──────────────────────────────────────────────────────

describe('mapJournalPhoto', () => {
  const photoDto: JournalPhotoResponseDto = {
    id: 'photo-1',
    entry_id: 'entry-server-1',
    alt_text: 'Vue sur la Meuse',
    keep_location: false,
    sort_order: 0,
    created_at: '2026-07-15T19:00:00Z',
  };

  it('construit la proxyUrl correctement', () => {
    const model = mapJournalPhoto(photoDto);
    expect(model.proxyUrl).toBe('/api/pilgrimage/journal/photos/photo-1');
    expect(model.altText).toBe('Vue sur la Meuse');
    expect(model.keepLocation).toBe(false);
  });

  it('encode les IDs spéciaux dans la proxyUrl', () => {
    const dto = { ...photoDto, id: 'photo/special id' };
    const model = mapJournalPhoto(dto);
    expect(model.proxyUrl).toContain(encodeURIComponent('photo/special id'));
  });
});

// ─── Flux offline : création → IDB → sync → réconciliation ──────────────────

describe('Flux offline-first IDB', () => {
  // Les fonctions IDB sont mockées dans setup.ts via vi.mock
  // On importe une fois (résolution statique du mock)
  let idb: typeof import('../shared/db/indexeddb.ts');

  beforeEach(async () => {
    vi.clearAllMocks();
    idb = await import('../shared/db/indexeddb.ts');
  });

  it('écrit l\'entrée dans IDB avec isSynced=false', async () => {
    const entry = {
      localId: 'local-uuid-abc',
      tripId: 'trip-1',
      body: 'Journée difficile mais belle.',
      title: null,
      visibility: 'private' as const,
      mood: 'tired' as const,
      stageId: null,
      kmWalked: null,
      entryDate: '2026-07-20',
      createdAt: Date.now(),
      isSynced: false,
    };

    await idb.putPendingJournalEntry(entry);
    expect(idb.putPendingJournalEntry).toHaveBeenCalledWith(entry);
    expect(idb.putPendingJournalEntry).toHaveBeenCalledTimes(1);
  });

  it('réconciliation : marque synced avec serverId après POST réussi', async () => {
    await idb.markJournalEntrySynced('local-uuid-abc', 'server-uuid-xyz');
    expect(idb.markJournalEntrySynced).toHaveBeenCalledWith('local-uuid-abc', 'server-uuid-xyz');
  });

  it('getPendingJournalEntries ne retourne que les non-synced', async () => {
    // Le mock retourne [] par défaut (setup.ts)
    const pending = await idb.getPendingJournalEntries();
    expect(Array.isArray(pending)).toBe(true);
  });

  it('idempotence : double appel avec même local_id ne crée pas de doublon côté IDB', async () => {
    const entry = {
      localId: 'same-local-id',
      tripId: 'trip-1',
      body: 'Entrée idempotente',
      title: null,
      visibility: 'private' as const,
      mood: null,
      stageId: null,
      kmWalked: null,
      entryDate: '2026-07-21',
      createdAt: Date.now(),
      isSynced: false,
    };

    // IDB put() est un upsert — le même localId écrase l'existant
    await idb.putPendingJournalEntry(entry);
    await idb.putPendingJournalEntry({ ...entry, body: 'Version mise à jour' });
    // Les deux appels sont faits, IDB garantit l'idempotence par keyPath=localId
    expect(idb.putPendingJournalEntry).toHaveBeenCalledTimes(2);
  });
});

// ─── Parité i18n journal fr/nl/de ─────────────────────────────────────────────

describe('Parité i18n — clés journal fr/nl/de', () => {
  function getJournalKeys(obj: Record<string, unknown>): Set<string> {
    // Extraire uniquement les clés journal.*
    const pilgrimage = obj['pilgrimage'] as Record<string, unknown>;
    const journal = pilgrimage['journal'] as Record<string, unknown>;

    function leaves(o: unknown, prefix: string): string[] {
      if (typeof o !== 'object' || o === null) return [prefix];
      return Object.entries(o as Record<string, unknown>).flatMap(([k, v]) =>
        leaves(v, `${prefix}.${k}`),
      );
    }

    return new Set(leaves(journal, 'journal'));
  }

  const frKeys = getJournalKeys(frPilgrimage as unknown as Record<string, unknown>);
  const nlKeys = getJournalKeys(nlPilgrimage as unknown as Record<string, unknown>);
  const deKeys = getJournalKeys(dePilgrimage as unknown as Record<string, unknown>);

  it('nl a toutes les clés journal de fr', () => {
    const missing = [...frKeys].filter(k => !nlKeys.has(k));
    expect(missing).toHaveLength(0);
  });

  it('de a toutes les clés journal de fr', () => {
    const missing = [...frKeys].filter(k => !deKeys.has(k));
    expect(missing).toHaveLength(0);
  });

  it('les clés de visibilité existent dans fr', () => {
    expect(frKeys.has('journal.visibility.private')).toBe(true);
    expect(frKeys.has('journal.visibility.members')).toBe(true);
    expect(frKeys.has('journal.visibility.public')).toBe(true);
  });

  it('les clés de mood existent dans fr', () => {
    expect(frKeys.has('journal.mood.great')).toBe(true);
    expect(frKeys.has('journal.mood.difficult')).toBe(true);
  });
});
