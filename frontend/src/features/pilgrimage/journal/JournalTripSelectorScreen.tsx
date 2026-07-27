/**
 * JournalTripSelectorScreen — /journal (sans tripId)
 * Affiche la liste des voyages du pèlerin et redirige vers /journal/:tripId.
 * - Un seul voyage → redirection directe.
 * - Aucun voyage → message + CTA créer un voyage.
 * ULTREIA-56b — Reliquat Phase C
 */

import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useMyTrips } from '../../../shared/hooks/useTrips.ts';
import { useAuth } from '../../../context/useAuth.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { TripModel } from '../../../models/pilgrimage.ts';

// ─── Carte de voyage ─────────────────────────────────────────────────────────

function TripSelectCard({
  trip,
  onSelect,
}: {
  trip: TripModel;
  onSelect: (id: string) => void;
}) {
  const { t } = useTranslation('pilgrimage');

  return (
    <button
      type="button"
      data-testid={`trip-select-card-${trip.id}`}
      onClick={() => onSelect(trip.id)}
      style={{
        width: '100%',
        textAlign: 'left',
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid var(--color-border-subtle)',
        padding: 'var(--space-4)',
        cursor: 'pointer',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-2)',
        minHeight: '72px',
      }}
    >
      <span style={{
        fontSize: 'var(--font-size-md)',
        fontWeight: 'var(--font-weight-semibold)',
        color: 'var(--color-text-primary)',
      }}>
        {trip.name}
      </span>
      <span style={{
        fontSize: 'var(--font-size-sm)',
        color: 'var(--color-text-secondary)',
      }}>
        {trip.route.name}
        {trip.estimatedStartDate ? ` · ${trip.estimatedStartDate}` : ''}
      </span>
      <span style={{
        fontSize: 'var(--font-size-xs)',
        color: 'var(--color-text-accent)',
      }}>
        {t(`trip.status.${trip.status}` as Parameters<typeof t>[0])}
      </span>
    </button>
  );
}

// ─── Écran principal ─────────────────────────────────────────────────────────

export function JournalTripSelectorScreen() {
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const { currentUser } = useAuth();

  const { data: trips, isLoading, isError } = useMyTrips();

  // Redirection automatique si un seul voyage
  useEffect(() => {
    if (!isLoading && !isError && trips && trips.length === 1) {
      const firstTrip = trips[0]; if (firstTrip) navigate(`/journal/${firstTrip.id}`, { replace: true });
    }
  }, [isLoading, isError, trips, navigate]);

  function handleSelect(tripId: string) {
    navigate(`/journal/${tripId}`);
  }

  if (!currentUser) {
    return (
      <div
        data-testid="journal-trip-selector"
        style={{ padding: 'var(--space-6)', textAlign: 'center', color: 'var(--color-text-tertiary)' }}
      >
        {t('auth.login_required')}
      </div>
    );
  }

  return (
    <div
      data-testid="journal-trip-selector"
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
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', padding: '0 var(--space-4)' }}>
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
      </header>

      {/* Contenu */}
      <div
        style={{
          flex: 1,
          overflowY: 'auto',
          WebkitOverflowScrolling: 'touch',
          padding: 'var(--space-4)',
          paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-4))',
          display: 'flex',
          flexDirection: 'column',
          gap: 'var(--space-3)',
        }}
      >
        {isLoading && <SkeletonCard count={3} />}

        {isError && (
          <EmptyState message={t('error.offline_fetch')} />
        )}

        {/* Sous-titre uniquement si plusieurs voyages */}
        {!isLoading && !isError && trips && trips.length > 1 && (
          <p style={{
            margin: 0,
            fontSize: 'var(--font-size-sm)',
            color: 'var(--color-text-secondary)',
          }}>
            {t('journal.select_trip')}
          </p>
        )}

        {/* Liste des voyages */}
        {!isLoading && !isError && trips && trips.length > 1 && (
          <div
            role="list"
            data-testid="trip-selector-list"
            aria-label={t('trip.my_trips_title')}
            style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}
          >
            {trips.map(trip => (
              <div key={trip.id} role="listitem">
                <TripSelectCard trip={trip} onSelect={handleSelect} />
              </div>
            ))}
          </div>
        )}

        {/* Aucun voyage */}
        {!isLoading && !isError && trips && trips.length === 0 && (
          <div
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: 'var(--space-4)',
              paddingTop: 'var(--space-8)',
            }}
          >
            <EmptyState message={t('journal.no_trip_yet')} />
            <button
              type="button"
              data-testid="create-trip-cta"
              onClick={() => navigate('/trips/new')}
              style={{
                minHeight: '44px',
                backgroundColor: 'var(--color-interactive-primary)',
                color: 'var(--color-text-inverse)',
                border: 'none',
                borderRadius: 'var(--radius-lg)',
                padding: '0 var(--space-6)',
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-semibold)',
                cursor: 'pointer',
              }}
            >
              {t('trip.new_trip')}
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
