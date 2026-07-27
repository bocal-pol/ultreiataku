/**
 * Hooks — Journal de voyage (offline-first)
 *
 * useSyncJournal : orchestrateur de sync
 *   - écoute window 'online'
 *   - écoute SW postMessage 'journal-sync-trigger'
 *   - polling 60 s fallback iOS
 *
 * Architecture sync :
 *   1. Écriture IDB (journal_pending) avec isSynced=false
 *   2. Si online → POST immédiat → markJournalEntrySynced
 *   3. Si offline → sync déclenchée au retour réseau
 *   4. Idempotence garantie par local_id (UUID v4)
 */

import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useCallback, useEffect, useRef } from 'react';
import { fetchJournalEntries, postJournalEntry, uploadJournalPhoto } from '../api/journal.ts';
import {
  getPendingJournalEntries,
  markJournalEntrySynced,
  getPendingPhotosForEntry,
  deletePendingPhoto,
} from '../db/indexeddb.ts';
import type { JournalEntriesPage } from '../api/journal.ts';
import type { JournalEntryModel } from '../../models/journal.ts';

// ─── Query keys ──────────────────────────────────────────────────────────────

export const journalKeys = {
  all: ['journal'] as const,
  entries: (tripId: string, tab: string) =>
    [...journalKeys.all, 'entries', tripId, tab] as const,
};

// ─── Tab filter ──────────────────────────────────────────────────────────────

export type JournalTab = 'all' | 'mine' | 'public';

// ─── useJournalEntries ────────────────────────────────────────────────────────

/**
 * I-05 — Le filtre tab === 'mine' est maintenant fonctionnel.
 * Le pilgrimId du pèlerin courant est passé en paramètre et filtré
 * côté client sur e.pilgrimId === currentPilgrimId.
 *
 * @param tripId     Identifiant du Trip
 * @param tab        Filtre actif : 'all' | 'mine' | 'public'
 * @param pilgrimId  ID du pèlerin courant (depuis useAuth().currentUser.pilgrim.id).
 *                   Requis pour que le filtre 'mine' soit opérant.
 *                   Si non fourni, 'mine' retourne toutes les entrées (dégradé).
 */
export function useJournalEntries(tripId: string, tab: JournalTab, pilgrimId?: string) {
  return useQuery<JournalEntriesPage, Error>({
    queryKey: journalKeys.entries(tripId, tab),
    queryFn: ({ signal }) => fetchJournalEntries(tripId, undefined, signal),
    enabled: tripId !== '',
    staleTime: 60 * 1000,
    select: (page): JournalEntriesPage => {
      if (tab === 'all') return page;
      const filtered = page.entries.filter((e: JournalEntryModel) => {
        if (tab === 'public') return e.visibility === 'public';
        // I-05 — 'mine' : comparaison avec le pilgrimId du pèlerin courant
        if (tab === 'mine') {
          // Dégradé si pilgrimId non fourni : affiche toutes les entrées
          if (!pilgrimId) return true;
          return e.pilgrimId === pilgrimId;
        }
        return true;
      });
      return { ...page, entries: filtered };
    },
    retry: (failureCount, error) => {
      if ('status' in error && (error as { status: number }).status === 401) return false;
      return failureCount < 2;
    },
  });
}

// ─── useSyncJournal ───────────────────────────────────────────────────────────

/**
 * Déclenche la synchronisation des entrées IDB non synced vers le backend.
 * À monter une seule fois dans JournalScreen.
 *
 * Cas limites gérés :
 * - Double POST → local_id idempotence (backend renvoie 200 si déjà existant)
 * - Conflit → updated_at_client envoyé, last-write-wins backend
 * - Photo orpheline → gcOrphanPhotos() GC 7 jours
 * - App fermée → reprise au prochain 'online' ou démarrage
 */
export function useSyncJournal(tripId: string) {
  const queryClient = useQueryClient();
  const isSyncingRef = useRef(false);

  const sync = useCallback(async () => {
    if (isSyncingRef.current) return;
    if (!navigator.onLine) return;

    isSyncingRef.current = true;
    try {
      const pending = await getPendingJournalEntries();
      if (pending.length === 0) return;

      for (const entry of pending) {
        if (entry.tripId !== tripId) continue;

        try {
          const result = await postJournalEntry({
            trip_id: entry.tripId,
            stage_id: entry.stageId,
            title: entry.title,
            body: entry.body,
            entry_date: entry.entryDate,
            visibility: entry.visibility,
            mood: entry.mood,
            km_walked_today: entry.kmWalked,
            local_id: entry.localId,
            updated_at_client: new Date(entry.createdAt).toISOString(),
          });

          await markJournalEntrySynced(entry.localId, result.id);

          // Upload photos en attente pour cette entrée
          const pendingPhotos = await getPendingPhotosForEntry(entry.localId);
          for (const photo of pendingPhotos) {
            try {
              const file = new File([photo.blob], 'photo.jpg', { type: photo.blob.type });
              await uploadJournalPhoto(result.id, file, photo.altText, photo.keepLocation);
              await deletePendingPhoto(photo.localId);
            } catch {
              // Photo upload échouée → laissée en IDB pour retry
            }
          }
        } catch {
          // Entrée non synced → laissée pour prochain cycle
        }
      }

      // Invalider le cache TanStack Query pour rafraîchir la liste
      await queryClient.invalidateQueries({ queryKey: journalKeys.entries(tripId, 'all') });
      await queryClient.invalidateQueries({ queryKey: journalKeys.entries(tripId, 'mine') });
      await queryClient.invalidateQueries({ queryKey: journalKeys.entries(tripId, 'public') });
    } finally {
      isSyncingRef.current = false;
    }
  }, [tripId, queryClient]);

  useEffect(() => {
    if (!tripId) return;

    // Déclencher au montage si online
    void sync();

    // Écoute 'online' window event
    const handleOnline = () => void sync();
    window.addEventListener('online', handleOnline);

    // Écoute SW postMessage 'journal-sync-trigger'
    const handleSwMessage = (event: MessageEvent) => {
      if (event.data?.type === 'journal-sync-trigger') {
        void sync();
      }
    };
    navigator.serviceWorker?.addEventListener('message', handleSwMessage);

    // Polling 60 s (fallback iOS qui ne dispatche pas toujours 'online')
    const interval = setInterval(() => void sync(), 60_000);

    return () => {
      window.removeEventListener('online', handleOnline);
      navigator.serviceWorker?.removeEventListener('message', handleSwMessage);
      clearInterval(interval);
    };
  }, [sync, tripId]);

  return { sync };
}
