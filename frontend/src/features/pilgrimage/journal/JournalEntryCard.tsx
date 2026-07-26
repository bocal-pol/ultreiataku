/**
 * JournalEntryCard — Card d'une entrée journal
 * Badges : photos / visibilité / sync
 */

import { useTranslation } from 'react-i18next';
import type { JournalEntryModel, JournalVisibility } from '../../../models/journal.ts';
import type { JournalPendingEntry } from '../../../models/journal.ts';

// ─── Badge visibilité ─────────────────────────────────────────────────────────

function VisibilityBadge({ visibility }: { visibility: JournalVisibility }) {
  const { t } = useTranslation('pilgrimage');

  const colorMap: Record<JournalVisibility, string> = {
    private: 'var(--color-text-tertiary)',
    members: 'var(--color-text-accent)',
    public: 'var(--color-camp-green, #5a9e5a)',
  };

  return (
    <span
      style={{
        fontSize: 'var(--font-size-xs)',
        color: colorMap[visibility],
        backgroundColor: `${colorMap[visibility]}18`,
        borderRadius: 'var(--radius-full)',
        padding: '1px 6px',
      }}
    >
      {t(`journal.visibility.${visibility}` as Parameters<typeof t>[0])}
    </span>
  );
}

// ─── Entrée synced (depuis serveur) ──────────────────────────────────────────

interface SyncedCardProps {
  entry: JournalEntryModel;
}

export function JournalEntryCard({ entry }: SyncedCardProps) {
  const { t } = useTranslation('pilgrimage');

  return (
    <article
      data-testid="journal-entry-card"
      style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid var(--color-border-subtle)',
        padding: 'var(--space-4)',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-2)',
      }}
    >
      {/* En-tête */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 'var(--space-2)', flexWrap: 'wrap' }}>
        <div>
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
            {entry.entryDate}
            {entry.stage && ` · ${entry.stage.name}`}
          </div>
          {entry.title && (
            <div style={{
              fontSize: 'var(--font-size-md)',
              fontWeight: 'var(--font-weight-semibold)',
              color: 'var(--color-text-primary)',
              marginTop: 'var(--space-1)',
            }}>
              {entry.title}
            </div>
          )}
        </div>

        {/* Badges */}
        <div style={{ display: 'flex', gap: 'var(--space-1)', flexWrap: 'wrap' }}>
          <VisibilityBadge visibility={entry.visibility} />
          {entry.photosCount > 0 && (
            <span
              aria-label={`${entry.photosCount} photo(s)`}
              style={{
                fontSize: 'var(--font-size-xs)',
                color: 'var(--color-text-tertiary)',
                backgroundColor: 'var(--color-bg-base)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-full)',
                padding: '1px 6px',
              }}
            >
              📷 {entry.photosCount}
            </span>
          )}
          {entry.mood && (
            <span
              style={{
                fontSize: 'var(--font-size-xs)',
                color: 'var(--color-text-tertiary)',
                padding: '1px 4px',
              }}
            >
              {t(`journal.mood.${entry.mood}` as Parameters<typeof t>[0])}
            </span>
          )}
        </div>
      </div>

      {/* Extrait */}
      {entry.body && (
        <p style={{
          margin: 0,
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-text-secondary)',
          display: '-webkit-box',
          WebkitLineClamp: 3,
          WebkitBoxOrient: 'vertical',
          overflow: 'hidden',
        }}>
          {entry.body}
        </p>
      )}

      {/* Auteur */}
      <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
        {entry.pilgrim.displayName}
      </div>
    </article>
  );
}

// ─── Entrée pending (depuis IDB) ─────────────────────────────────────────────

interface PendingCardProps {
  entry: JournalPendingEntry;
}

export function JournalPendingEntryCard({ entry }: PendingCardProps) {
  const { t } = useTranslation('pilgrimage');

  return (
    <article
      data-testid="journal-entry-card"
      style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px dashed var(--color-border-subtle)',
        padding: 'var(--space-4)',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-2)',
        opacity: 0.85,
      }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 'var(--space-2)' }}>
        <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
          {entry.entryDate}
        </div>
        <span
          data-testid="journal-sync-badge"
          style={{
            fontSize: 'var(--font-size-xs)',
            color: 'var(--color-detour-amber, #d4840a)',
            backgroundColor: 'rgba(212,132,10,0.1)',
            borderRadius: 'var(--radius-full)',
            padding: '2px 8px',
          }}
        >
          {t('journal.offline_notice')}
        </span>
      </div>

      {entry.title && (
        <div style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-primary)',
        }}>
          {entry.title}
        </div>
      )}

      {entry.body && (
        <p style={{
          margin: 0,
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-text-secondary)',
          display: '-webkit-box',
          WebkitLineClamp: 3,
          WebkitBoxOrient: 'vertical',
          overflow: 'hidden',
        }}>
          {entry.body}
        </p>
      )}
    </article>
  );
}
