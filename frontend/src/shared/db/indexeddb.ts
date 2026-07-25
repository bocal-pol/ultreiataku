/**
 * IndexedDB — Ultreiataku offline store
 * Stores : stages, waypoints, gpx, journal_pending
 */

import { openDB, type DBSchema, type IDBPDatabase } from 'idb';
import type { StageDetailModel } from '../../models/pilgrimage.ts';
import type { GpxLineModel } from '../../models/pilgrimage.ts';

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
      visibility: 'private' | 'members' | 'public';
      mood: string | null;
      stageId: string | null;
      kmWalked: number | null;
      entryDate: string;
      createdAt: number;
      isSynced: boolean;
    };
  };
}

const DB_NAME = 'ultreiataku';
const DB_VERSION = 1;

let dbInstance: IDBPDatabase<UltreiaDB> | null = null;

export async function getDb(): Promise<IDBPDatabase<UltreiaDB>> {
  if (dbInstance) return dbInstance;
  dbInstance = await openDB<UltreiaDB>(DB_NAME, DB_VERSION, {
    upgrade(db) {
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
    },
  });
  return dbInstance;
}

export async function cacheStage(stage: StageDetailModel): Promise<void> {
  const db = await getDb();
  await db.put('stages', stage);
}

export async function getCachedStage(code: string): Promise<StageDetailModel | undefined> {
  const db = await getDb();
  return db.get('stages', code);
}

export async function cacheGpx(id: string, data: GpxLineModel): Promise<void> {
  const db = await getDb();
  await db.put('gpx', { id, data, cachedAt: Date.now() });
}

export async function getCachedGpx(id: string): Promise<GpxLineModel | null> {
  const db = await getDb();
  const entry = await db.get('gpx', id);
  if (!entry) return null;
  // GeoJSON frais 7 jours
  const sevenDays = 7 * 24 * 60 * 60 * 1000;
  if (Date.now() - entry.cachedAt > sevenDays) return null;
  return entry.data;
}

export async function getPendingJournalEntries(): Promise<UltreiaDB['journal_pending']['value'][]> {
  const db = await getDb();
  const all = await db.getAll('journal_pending');
  return all.filter(e => !e.isSynced);
}

export async function putPendingJournalEntry(
  entry: UltreiaDB['journal_pending']['value'],
): Promise<void> {
  const db = await getDb();
  await db.put('journal_pending', entry);
}

export async function markJournalEntrySynced(localId: string): Promise<void> {
  const db = await getDb();
  const entry = await db.get('journal_pending', localId);
  if (entry) {
    await db.put('journal_pending', { ...entry, isSynced: true });
  }
}
