/**
 * JournalEntryFormScreen — Formulaire nouvelle entrée journal (offline-first)
 * ULTREIA-55 — Vague 1e
 *
 * Flux offline-first :
 * 1. Écriture IDB immédiate avec UUID v4 + isSynced=false
 * 2. Si online → POST backend → réconciliation {id, local_id, synced_at}
 * 3. Si offline → badge "en attente", sync auto au retour réseau (useSyncJournal)
 */

import { useState, useId } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { useAuth } from '../../../context/AuthContext.tsx';
import {
  putPendingJournalEntry,
  markJournalEntrySynced,
  putPendingPhoto,
} from '../../../shared/db/indexeddb.ts';
import { postJournalEntry, uploadJournalPhoto } from '../../../shared/api/journal.ts';
import { journalKeys } from '../../../shared/hooks/useJournal.ts';
import type { JournalVisibility, JournalMood } from '../../../models/journal.ts';
import type { StageModel } from '../../../models/pilgrimage.ts';
import { PhotoUploader, type PhotoUploadValue } from './PhotoUploader.tsx';

// ─── UUID v4 sans dépendance externe ─────────────────────────────────────────
function uuidV4(): string {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
    const r = (Math.random() * 16) | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

// ─── Props ────────────────────────────────────────────────────────────────────
interface JournalEntryFormScreenProps {
  /** Stages disponibles pour le select (provient du TripModel via JournalScreen) */
  stages?: StageModel[];
}

// ─── Composant principal ──────────────────────────────────────────────────────

export function JournalEntryFormScreen({ stages = [] }: JournalEntryFormScreenProps) {
  const { tripId } = useParams<{ tripId: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const { currentUser } = useAuth();
  const queryClient = useQueryClient();

  // Champs formulaire
  const [title, setTitle] = useState('');
  const [body, setBody] = useState('');
  const [visibility, setVisibility] = useState<JournalVisibility>('private');
  const [stageId, setStageId] = useState('');
  const [mood, setMood] = useState<JournalMood | ''>('');
  const [entryDate, setEntryDate] = useState(() => new Date().toISOString().slice(0, 10));
  const [photo, setPhoto] = useState<PhotoUploadValue | null>(null);

  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // IDs accessibilité
  const titleId = useId();
  const bodyId = useId();
  const visId = useId();
  const stageSelectId = useId();
  const moodId = useId();
  const dateId = useId();

  const isOnline = navigator.onLine;

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    if (!tripId) {
      setError('Trip introuvable.');
      return;
    }
    if (!body.trim() && !title.trim()) {
      setError('Le texte ou le titre est requis.');
      return;
    }

    setSaving(true);
    const localId = uuidV4();
    const now = Date.now();

    try {
      // 1. Écriture IDB immédiate (toujours, online ou offline)
      await putPendingJournalEntry({
        localId,
        tripId,
        body: body.trim(),
        title: title.trim() || null,
        visibility,
        mood: mood || null,
        stageId: stageId || null,
        kmWalked: null,
        entryDate,
        createdAt: now,
        isSynced: false,
      });

      // Stocker photo en IDB si présente
      if (photo) {
        const photoLocalId = uuidV4();
        await putPendingPhoto({
          localId: photoLocalId,
          entryLocalId: localId,
          blob: photo.file,
          altText: photo.altText || null,
          keepLocation: photo.keepLocation,
        });
      }

      // 2. Si online → POST immédiat
      if (isOnline) {
        try {
          const result = await postJournalEntry({
            trip_id: tripId,
            stage_id: stageId || null,
            title: title.trim() || null,
            body: body.trim(),
            entry_date: entryDate,
            visibility,
            mood: mood || null,
            km_walked_today: null,
            local_id: localId,
            updated_at_client: new Date(now).toISOString(),
          });

          await markJournalEntrySynced(localId, result.id);

          // Upload photo si présente
          if (photo) {
            try {
              await uploadJournalPhoto(result.id, photo.file, photo.altText || null, photo.keepLocation);
            } catch {
              // Photo upload échouée → laissée en IDB (journal_photo_pending) pour retry
            }
          }

          // Invalider le cache pour rafraîchir la liste
          void queryClient.invalidateQueries({ queryKey: journalKeys.entries(tripId, 'all') });
          void queryClient.invalidateQueries({ queryKey: journalKeys.entries(tripId, 'mine') });
        } catch {
          // Sync échouée → entrée reste en IDB, sync au retour réseau (useSyncJournal)
        }
      }

      navigate(`/journal/${tripId}`);
    } catch (err) {
      setError(err instanceof Error ? err.message : t('error.sync_failed'));
    } finally {
      setSaving(false);
    }
  }

  if (!currentUser) {
    return (
      <div style={{ padding: 'var(--space-6)', textAlign: 'center', color: 'var(--color-text-tertiary)' }}>
        {t('auth.login_required')}
      </div>
    );
  }

  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
      }}
    >
      {/* Header */}
      <header style={{
        position: 'sticky',
        top: 0,
        zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', gap: 'var(--space-3)' }}>
          <button
            type="button"
            onClick={() => navigate(-1)}
            aria-label={t('journal.cancel')}
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              color: 'var(--color-text-accent)', padding: '8px',
              borderRadius: 'var(--radius-md)',
              display: 'flex', alignItems: 'center',
              minWidth: '44px', minHeight: '44px', justifyContent: 'center',
            }}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6" />
            </svg>
          </button>
          <h1 style={{
            flex: 1,
            margin: 0,
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            color: 'var(--color-text-primary)',
            fontFamily: 'var(--font-family-interface)',
          }}>
            {t('journal.new_entry')}
          </h1>

          {/* Indicateur hors ligne */}
          {!isOnline && (
            <span style={{
              fontSize: 'var(--font-size-xs)',
              color: 'var(--color-detour-amber, #d4840a)',
              backgroundColor: 'rgba(212,132,10,0.1)',
              borderRadius: 'var(--radius-full)',
              padding: '2px 8px',
            }}>
              {t('offline.banner')}
            </span>
          )}
        </div>
      </header>

      {/* Formulaire */}
      <form
        data-testid="journal-form"
        onSubmit={handleSubmit}
        noValidate
        style={{
          flex: 1,
          overflowY: 'auto',
          WebkitOverflowScrolling: 'touch',
          padding: 'var(--space-4)',
          paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-8))',
          display: 'flex',
          flexDirection: 'column',
          gap: 'var(--space-4)',
        }}
      >
        {error && (
          <div
            role="alert"
            style={{
              fontSize: 'var(--font-size-sm)',
              color: 'var(--color-error, #e8503a)',
              backgroundColor: 'rgba(232,80,58,0.08)',
              padding: 'var(--space-2) var(--space-3)',
              borderRadius: 'var(--radius-md)',
            }}
          >
            {error}
          </div>
        )}

        {/* Titre (facultatif) */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
          <label htmlFor={titleId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
            {t('journal.placeholder_title')}
          </label>
          <input
            id={titleId}
            type="text"
            value={title}
            onChange={e => setTitle(e.target.value)}
            placeholder={t('journal.placeholder_title')}
            style={{
              minHeight: '44px',
              backgroundColor: 'var(--color-bg-elevated)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-md)',
              padding: '0 var(--space-3)',
              fontSize: 'var(--font-size-sm)',
              color: 'var(--color-text-primary)',
              boxSizing: 'border-box',
              width: '100%',
            }}
          />
        </div>

        {/* Corps */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
          <label htmlFor={bodyId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
            {t('journal.title')}
          </label>
          <textarea
            id={bodyId}
            data-testid="journal-body-input"
            value={body}
            onChange={e => setBody(e.target.value)}
            placeholder={t('journal.placeholder_body')}
            rows={6}
            style={{
              backgroundColor: 'var(--color-bg-elevated)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-md)',
              padding: 'var(--space-3)',
              fontSize: 'var(--font-size-sm)',
              color: 'var(--color-text-primary)',
              resize: 'vertical',
              boxSizing: 'border-box',
              width: '100%',
              minHeight: '120px',
              fontFamily: 'inherit',
            }}
          />
        </div>

        {/* Date */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
          <label htmlFor={dateId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
            {t('trip.departure_date')}
          </label>
          <input
            id={dateId}
            type="date"
            value={entryDate}
            onChange={e => setEntryDate(e.target.value)}
            required
            aria-required="true"
            style={{
              minHeight: '44px',
              backgroundColor: 'var(--color-bg-elevated)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-md)',
              padding: '0 var(--space-3)',
              fontSize: 'var(--font-size-sm)',
              color: 'var(--color-text-primary)',
              boxSizing: 'border-box',
              width: '100%',
            }}
          />
        </div>

        {/* Visibilité */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
          <label htmlFor={visId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
            {t('journal.visibility.label')}
          </label>
          <select
            id={visId}
            data-testid="journal-visibility-select"
            value={visibility}
            onChange={e => setVisibility(e.target.value as JournalVisibility)}
            style={{
              minHeight: '44px',
              backgroundColor: 'var(--color-bg-elevated)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-md)',
              padding: '0 var(--space-3)',
              fontSize: 'var(--font-size-sm)',
              color: 'var(--color-text-primary)',
              boxSizing: 'border-box',
              width: '100%',
            }}
          >
            <option value="private">{t('journal.visibility.private')} — vu par vous seul</option>
            <option value="members">{t('journal.visibility.members')} — vos compagnons de voyage</option>
            <option value="public">{t('journal.visibility.public')} — famille et proches</option>
          </select>
        </div>

        {/* Étape (optionnel) */}
        {stages.length > 0 && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
            <label htmlFor={stageSelectId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
              {t('trip.departure_start')} (facultatif)
            </label>
            <select
              id={stageSelectId}
              data-testid="journal-stage-select"
              value={stageId}
              onChange={e => setStageId(e.target.value)}
              style={{
                minHeight: '44px',
                backgroundColor: 'var(--color-bg-elevated)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-md)',
                padding: '0 var(--space-3)',
                fontSize: 'var(--font-size-sm)',
                color: stageId ? 'var(--color-text-primary)' : 'var(--color-text-tertiary)',
                boxSizing: 'border-box',
                width: '100%',
              }}
            >
              <option value="">Aucune étape</option>
              {stages.map(s => (
                <option key={s.id} value={s.id}>J{s.dayNumber} — {s.name}</option>
              ))}
            </select>
          </div>
        )}

        {/* Humeur */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
          <label htmlFor={moodId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
            {t('journal.mood.label')}
          </label>
          <select
            id={moodId}
            data-testid="journal-mood-select"
            value={mood}
            onChange={e => setMood(e.target.value as JournalMood | '')}
            style={{
              minHeight: '44px',
              backgroundColor: 'var(--color-bg-elevated)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-md)',
              padding: '0 var(--space-3)',
              fontSize: 'var(--font-size-sm)',
              color: mood ? 'var(--color-text-primary)' : 'var(--color-text-tertiary)',
              boxSizing: 'border-box',
              width: '100%',
            }}
          >
            <option value="">—</option>
            <option value="great">{t('journal.mood.great')}</option>
            <option value="good">{t('journal.mood.good')}</option>
            <option value="neutral">{t('journal.mood.neutral')}</option>
            <option value="tired">{t('journal.mood.tired')}</option>
            <option value="difficult">{t('journal.mood.difficult')}</option>
          </select>
        </div>

        {/* Photo */}
        <PhotoUploader value={photo} onChange={setPhoto} />

        {/* Bouton sauvegarder */}
        <button
          type="submit"
          data-testid="journal-save-btn"
          disabled={saving}
          aria-busy={saving}
          style={{
            minHeight: '56px',
            backgroundColor: 'var(--color-interactive-primary)',
            color: 'var(--color-text-inverse)',
            border: 'none',
            borderRadius: 'var(--radius-lg)',
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            cursor: saving ? 'wait' : 'pointer',
            opacity: saving ? 0.7 : 1,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            gap: 'var(--space-1)',
          }}
        >
          <span>{saving ? '…' : t('journal.save')}</span>
          {!isOnline && !saving && (
            <span style={{ fontSize: 'var(--font-size-xs)', opacity: 0.8 }}>
              {t('error.offline_save')}
            </span>
          )}
        </button>
      </form>
    </div>
  );
}
