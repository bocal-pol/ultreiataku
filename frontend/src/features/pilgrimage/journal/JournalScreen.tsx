/**
 * JournalScreen — /journal/:tripId
 * Liste chronologique des entrées journal, tabs, FAB nouvelle entrée.
 * ULTREIA-56 — Vague 1e
 *
 * Monte useSyncJournal pour la synchronisation offline automatique.
 */

import { useState, useEffect, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../../context/useAuth.ts';
import {
  useJournalEntries,
  useSyncJournal,
  type JournalTab,
} from '../../../shared/hooks/useJournal.ts';
import { getAllJournalEntries } from '../../../shared/db/indexeddb.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import { JournalEntryCard, JournalPendingEntryCard } from './JournalEntryCard.tsx';
import type { JournalPendingEntry } from '../../../models/journal.ts';



// ─── Tab bar ──────────────────────────────────────────────────────────────────

interface TabBarProps {
  activeTab: JournalTab;
  onTabChange: (tab: JournalTab) => void;
}

function TabBar({ activeTab, onTabChange }: TabBarProps) {
  const { t } = useTranslation('pilgrimage');

  const tabs: { key: JournalTab; label: string; testId: string }[] = [
    { key: 'all', label: t('journal.tab_all'), testId: 'journal-tab-all' },
    { key: 'mine', label: t('journal.tab_mine'), testId: 'journal-tab-mine' },
    { key: 'public', label: t('journal.tab_public'), testId: 'journal-tab-public' },
  ];

  return (
    <div
      role="tablist"
      aria-label={t('journal.title')}
      style={{
        display: 'flex',
        borderBottom: '1px solid var(--color-border-subtle)',
        backgroundColor: 'var(--color-bg-elevated)',
      }}
    >
      {tabs.map(tab => (
        <button
          key={tab.key}
          type="button"
          role="tab"
          id={`tab-${tab.key}`}
          aria-selected={activeTab === tab.key}
          aria-controls={`tabpanel-${tab.key}`}
          data-testid={tab.testId}
          onClick={() => onTabChange(tab.key)}
          style={{
            flex: 1,
            minHeight: '44px',
            background: 'none',
            border: 'none',
            borderBottom: activeTab === tab.key
              ? '2px solid var(--color-gold-500)'
              : '2px solid transparent',
            cursor: 'pointer',
            fontSize: 'var(--font-size-sm)',
            fontWeight: activeTab === tab.key
              ? 'var(--font-weight-semibold)'
              : 'var(--font-weight-normal)',
            color: activeTab === tab.key
              ? 'var(--color-text-primary)'
              : 'var(--color-text-tertiary)',
            transition: 'color 0.15s, border-color 0.15s',
          }}
        >
          {tab.label}
        </button>
      ))}
    </div>
  );
}

// ─── Compteur pending ─────────────────────────────────────────────────────────

function PendingBanner({ count }: { count: number }) {
  const { t } = useTranslation('pilgrimage');
  if (count === 0) return null;
  return (
    <div
      role="status"
      aria-live="polite"
      style={{
        fontSize: 'var(--font-size-xs)',
        color: 'var(--color-detour-amber, #d4840a)',
        backgroundColor: 'rgba(212,132,10,0.08)',
        padding: 'var(--space-2) var(--space-4)',
        textAlign: 'center',
      }}
    >
      {t(count === 1 ? 'journal.sync_pending_one' : 'journal.sync_pending_other', { count })}
    </div>
  );
}

// ─── Écran principal ─────────────────────────────────────────────────────────

export function JournalScreen() {
  const { tripId } = useParams<{ tripId: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const { currentUser } = useAuth();

  const [activeTab, setActiveTab] = useState<JournalTab>('all');
  const [pendingEntries, setPendingEntries] = useState<JournalPendingEntry[]>([]);

  const resolvedTripId = tripId ?? '';

  // Données serveur
  const { data: page, isLoading, isError } = useJournalEntries(resolvedTripId, activeTab);

  // Sync offline
  useSyncJournal(resolvedTripId);

  // Charger les entrées IDB pending
  const mountedRef = useRef(true);
  useEffect(() => {
    mountedRef.current = true;
    async function loadPending() {
      const all = await getAllJournalEntries();
      if (!mountedRef.current) return;
      const pending = all
        .filter(e => !e.isSynced && e.tripId === resolvedTripId)
        .map(e => ({
          localId: e.localId,
          tripId: e.tripId,
          body: e.body,
          title: e.title,
          visibility: e.visibility,
          mood: e.mood,
          stageId: e.stageId,
          kmWalked: e.kmWalked,
          entryDate: e.entryDate,
          createdAt: e.createdAt,
          isSynced: e.isSynced,
          serverId: e.serverId,
        }));
      setPendingEntries(pending);
    }
    void loadPending();
    return () => { mountedRef.current = false; };
  }, [resolvedTripId, page]); // re-check après rafraîchissement serveur

  if (!currentUser) {
    return (
      <div style={{ padding: 'var(--space-6)', textAlign: 'center', color: 'var(--color-text-tertiary)' }}>
        {t('auth.login_required')}
      </div>
    );
  }

  // Filtrer "mine" côté client (server renvoie toutes les visibles)
  const filteredEntries = activeTab === 'mine'
    ? (page?.entries ?? []).filter(e => e.pilgrimId === currentUser.pilgrim.id)
    : (page?.entries ?? []);

  return (
    <div
      data-testid="journal-screen"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
        position: 'relative',
      }}
    >
      {/* Header */}
      <header style={{
        position: 'sticky',
        top: 0,
        zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', padding: '0 var(--space-4)' }}>
          <button
            type="button"
            onClick={() => navigate(-1)}
            aria-label="Retour"
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
            {t('journal.title')}
          </h1>
        </div>

        {/* Tabs */}
        <TabBar activeTab={activeTab} onTabChange={setActiveTab} />

        {/* Pending banner */}
        <PendingBanner count={pendingEntries.length} />
      </header>

      {/* Contenu */}
      <div
        id={`tabpanel-${activeTab}`}
        role="tabpanel"
        aria-labelledby={`tab-${activeTab}`}
        style={{
          flex: 1,
          overflowY: 'auto',
          WebkitOverflowScrolling: 'touch',
          padding: 'var(--space-4)',
          paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + 80px)',
          display: 'flex',
          flexDirection: 'column',
          gap: 'var(--space-3)',
        }}
      >
        {isLoading && <SkeletonCard count={4} />}

        {isError && (
          <EmptyState message={t('error.offline_fetch')} />
        )}

        {/* Entrées pending (offline) en premier */}
        {!isLoading && pendingEntries.map(entry => (
          <JournalPendingEntryCard key={entry.localId} entry={entry} />
        ))}

        {/* Entrées serveur */}
        {!isLoading && !isError && filteredEntries.map(entry => (
          <JournalEntryCard key={entry.id} entry={entry} />
        ))}

        {/* Empty state */}
        {!isLoading && !isError && filteredEntries.length === 0 && pendingEntries.length === 0 && (
          <EmptyState message={
            activeTab === 'public'
              ? t('journal.empty_public')
              : t('journal.empty_all')
          } />
        )}
      </div>

      {/* FAB nouvelle entrée */}
      <button
        type="button"
        data-testid="journal-fab"
        aria-label={t('journal.new_entry')}
        onClick={() => navigate(`/journal/${resolvedTripId}/new`)}
        style={{
          position: 'fixed',
          bottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-4))',
          right: 'var(--space-4)',
          width: '56px',
          height: '56px',
          borderRadius: '50%',
          backgroundColor: 'var(--color-interactive-primary)',
          color: 'var(--color-text-inverse)',
          border: 'none',
          cursor: 'pointer',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '24px',
          boxShadow: '0 4px 12px rgba(0,0,0,0.25)',
          zIndex: 200,
        }}
      >
        <span aria-hidden="true">+</span>
      </button>


    </div>
  );
}
