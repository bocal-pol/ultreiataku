/**
 * IndexedDB — Ultreiataku offline store
 * Stores : stages, waypoints, gpx, journal_pending, journal_photo_pending
 *
 * Version 1 → 2 : ajout de journal_photo_pending
 */

import { openDB, type DBSchema, type IDBPDatabase } from 'idb';
import type { StageDetailModel } from '../../models/pilgrimage.ts';
import type { GpxLineModel } from '../../models/pilgrimage.ts';
import type { JournalVisibility, JournalMood } from '../../models/journal.ts';

interface UltreiaDB extends DBSchema {
  stages: {
    key: string;
    value: StageDetailModel;
    indexes: { by_country: string };
  };
  gpx: {
    key: string;
    value: { id: string; data: GpxLineModel; cachedAt: number };
  };
  journal_pending: {
    key: string;
    value: {
      localId: string;
      tripId: string;
      body: string;
      title: string | null;
      visibility: JournalVisibility;
      mood: JournalMood | null;
      stageId: string | null;
      kmWalked: number | null;
      entryDate: string;
      createdAt: number;
      isSynced: boolean;
      /** ID serveur après sync réussie */
      serverId?: string;
    };
  };
  journal_photo_pending: {
    key: string;
    value: {
      localId: string;
      entryLocalId: string;
      blob: Blob;
      altText: string | null;
      keepLocation: boolean;
    };
  };
}

const DB_NAME = 'ultreiataku';
const DB_VERSION = 2;

let dbInstance: IDBPDatabase<UltreiaDB> | null = null;

export async function getDb(): Promise<IDBPDatabase<UltreiaDB>> {
  if (dbInstance) return dbInstance;
  dbInstance = await openDB<UltreiaDB>(DB_NAME, DB_VERSION, {
    upgrade(db, oldVersion) {
      // Version 1 — stores originaux
      if (oldVersion < 1) {
        if (!db.objectStoreNames.contains('stages')) {
          const stageStore = db.createObjectStore('stages', { keyPath: 'code' });
          stageStore.createIndex('by_country', 'code');
        }
        if (!db.objectStoreNames.contains('gpx')) {
          db.createObjectStore('gpx', { keyPath: 'id' });
        }
        if (!db.objectStoreNames.contains('journal_pending')) {
          db.createObjectStore('journal_pending', { keyPath: 'localId' });
        }
      }
      // Version 2 — ajout journal_photo_pending
      if (oldVersion < 2) {
        if (!db.objectStoreNames.contains('journal_photo_pending')) {
          db.createObjectStore('journal_photo_pending', { keyPath: 'localId' });
        }
      }
    },
  });
  return dbInstance;
}

// ─── Stages ──────────────────────────────────────────────────────────────────

export async function cacheStage(stage: StageDetailModel): Promise<void> {
  const db = await getDb();
  await db.put('stages', stage);
}

export async function getCachedStage(code: string): Promise<StageDetailModel | undefined> {
  const db = await getDb();
  return db.get('stages', code);
}

// ─── GPX ─────────────────────────────────────────────────────────────────────

export async function cacheGpx(id: string, data: GpxLineModel): Promise<void> {
  const db = await getDb();
  await db.put('gpx', { id, data, cachedAt: Date.now() });
}

export async function getCachedGpx(id: string): Promise<GpxLineModel | null> {
  const db = await getDb();
  const entry = await db.get('gpx', id);
  if (!entry) return null;
  const sevenDays = 7 * 24 * 60 * 60 * 1000;
  if (Date.now() - entry.cachedAt > sevenDays) return null;
  return entry.data;
}

// ─── Journal pending ──────────────────────────────────────────────────────────

export type JournalPendingRecord = UltreiaDB['journal_pending']['value'];

export async function getPendingJournalEntries(): Promise<JournalPendingRecord[]> {
  const db = await getDb();
  const all = await db.getAll('journal_pending');
  return all.filter(e => !e.isSynced);
}

export async function getAllJournalEntries(): Promise<JournalPendingRecord[]> {
  const db = await getDb();
  return db.getAll('journal_pending');
}

export async function putPendingJournalEntry(
  entry: JournalPendingRecord,
): Promise<void> {
  const db = await getDb();
  await db.put('journal_pending', entry);
}

export async function markJournalEntrySynced(
  localId: string,
  serverId: string,
): Promise<void> {
  const db = await getDb();
  const entry = await db.get('journal_pending', localId);
  if (entry) {
    await db.put('journal_pending', { ...entry, isSynced: true, serverId });
  }
}

export async function getJournalEntry(localId: string): Promise<JournalPendingRecord | undefined> {
  const db = await getDb();
  return db.get('journal_pending', localId);
}

// ─── Journal photo pending ────────────────────────────────────────────────────

export type JournalPhotoPendingRecord = UltreiaDB['journal_photo_pending']['value'];

export async function getPendingPhotosForEntry(
  entryLocalId: string,
): Promise<JournalPhotoPendingRecord[]> {
  const db = await getDb();
  const all = await db.getAll('journal_photo_pending');
  return all.filter(p => p.entryLocalId === entryLocalId);
}

export async function putPendingPhoto(
  photo: JournalPhotoPendingRecord,
): Promise<void> {
  const db = await getDb();
  await db.put('journal_photo_pending', photo);
}

export async function deletePendingPhoto(localId: string): Promise<void> {
  const db = await getDb();
  await db.delete('journal_photo_pending', localId);
}

/**
 * Garbage collect : supprime les photos dont l'entrée est plus vieille de 7 jours
 * et dont l'entrée n'a jamais été synced (entrée orpheline).
 */
export async function gcOrphanPhotos(): Promise<void> {
  const db = await getDb();
  const sevenDays = 7 * 24 * 60 * 60 * 1000;
  const now = Date.now();

  const allEntries = await db.getAll('journal_pending');
  const orphanLocalIds = new Set(
    allEntries
      .filter(e => !e.isSynced && now - e.createdAt > sevenDays)
      .map(e => e.localId),
  );

  if (orphanLocalIds.size === 0) return;

  const allPhotos = await db.getAll('journal_photo_pending');
  for (const photo of allPhotos) {
    if (orphanLocalIds.has(photo.entryLocalId)) {
      await db.delete('journal_photo_pending', photo.localId);
    }
  }
}
