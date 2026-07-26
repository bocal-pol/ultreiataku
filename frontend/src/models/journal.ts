/**
 * Models UI — Journal de voyage
 * Types consommés par les composants React.
 * Jamais de DTO brut ici.
 */

import type { PilgrimModel, StageModel } from './pilgrimage.ts';

export type JournalVisibility = 'private' | 'members' | 'public';

export type JournalMood = 'great' | 'good' | 'neutral' | 'tired' | 'difficult';

export interface JournalPhotoModel {
  id: string;
  entryId: string;
  altText: string | null;
  keepLocation: boolean;
  sortOrder: number | null;
  createdAt: string;
  /** URL via proxy backend — jamais URL MinIO directe */
  proxyUrl: string;
}

export interface JournalEntryModel {
  id: string;
  tripId: string;
  pilgrimId: string | null;
  stageId: string | null;
  title: string | null;
  body: string | null;
  entryDate: string;
  latitude: number | null;
  longitude: number | null;
  visibility: JournalVisibility;
  mood: JournalMood | null;
  kmWalkedToday: number | null;
  isSynced: boolean;
  localId: string | null;
  photosCount: number;
  photos: JournalPhotoModel[];
  pilgrim: PilgrimModel;
  stage: StageModel | null;
  createdAt: string;
  updatedAt: string;
}

/**
 * Entrée en attente de synchronisation (miroir du store IDB journal_pending).
 * Utilisée dans les composants pour afficher les entrées offline.
 */
export interface JournalPendingEntry {
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
}

/**
 * Photo en attente d'upload (store IDB journal_photo_pending).
 */
export interface JournalPhotoPending {
  localId: string;
  entryLocalId: string;
  blob: Blob;
  altText: string | null;
  keepLocation: boolean;
}
