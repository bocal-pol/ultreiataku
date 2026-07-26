/**
 * MyTripsScreen — /trips
 * Liste des voyages de l'utilisateur connecté.
 * ULTREIA-36
 */

import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useMyTrips } from '../../../shared/hooks/useTrips.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { TripModel } from '../../../models/pilgrimage.ts';

function TripStatusBadge({ status }: { status: TripModel['status'] }) {
  const { t } = useTranslation('pilgrimage');
  const colors: Record<TripModel['status'], string> = {
    planned:   'var(--color-text-accent)',
    active:    'var(--color-camp-green)',
    completed: 'var(--color-text-tertiary)',
    cancelled: 'var(--color-detour-amber)',
  };
  return (
    <span
      style={{
        fontSize: 'var(--font-size-xs)',
        color: colors[status],
        backgroundColor: 'rgba(200,150,60,0.08)',
        borderRadius: 'var(--radius-full)',
        padding: '2px 8px',
        border: `1px solid ${colors[status]}40`,
        flexShrink: 0,
      }}
    >
      {t(`trip.status.${status}` as Parameters<typeof t>[0])}
    </span>
  );
}

function TripCard({ trip }: { trip: TripModel }) {
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');

  // Trouver le rôle de l'utilisateur courant (organizer si son id est dans les membres)
  const myMember = trip.members[0];
  const roleKey = myMember?.role ?? 'participant';

  return (
    <article
      role="listitem"
      data-testid={`trip-card-${trip.id}`}
      tabIndex={0}
      onClick={() => navigate(`/trips/${trip.id}`)}
      onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') navigate(`/trips/${trip.id}`); }}
      aria-label={`${trip.name}, ${t(`trip.status.${trip.status}` as Parameters<typeof t>[0])}`}
      style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid var(--color-border-subtle)',
        padding: 'var(--space-4)',
        marginBottom: 'var(--space-3)',
        cursor: 'pointer',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-2)',
        minHeight: '72px',
      }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 'var(--space-2)' }}>
        <h2 style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-primary)',
          margin: 0,
          flex: 1,
          minWidth: 0,
        }}>
          {trip.name}
        </h2>
        <TripStatusBadge status={trip.status} />
      </div>

      <div style={{
        fontSize: 'var(--font-size-sm)',
        color: 'var(--color-text-secondary)',
        display: 'flex',
        gap: 'var(--space-3)',
        flexWrap: 'wrap',
      }}>
        <span>{trip.route.name}</span>
        {trip.estimatedStartDate && (
          <span>· {trip.estimatedStartDate}</span>
        )}
      </div>

      <div style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-2)' }}>
        <span style={{
          fontSize: 'var(--font-size-xs)',
          color: 'var(--color-text-tertiary)',
          backgroundColor: 'rgba(200,150,60,0.06)',
          borderRadius: 'var(--radius-full)',
          padding: '1px 6px',
        }}>
          {t(`trip.roles.${roleKey}` as Parameters<typeof t>[0])}
        </span>
        <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
          · {trip.members.length} {trip.members.length > 1 ? 'membres' : 'membre'}
        </span>
      </div>
    </article>
  );
}

export function MyTripsScreen() {
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const { data: trips, isLoading, isError } = useMyTrips();

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%', backgroundColor: 'var(--color-bg-base)' }}>
      {/* Header */}
      <header style={{
        position: 'sticky', top: 0, zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', justifyContent: 'space-between' }}>
          <h1 style={{
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            color: 'var(--color-text-primary)',
            fontFamily: 'var(--font-family-interface)',
            margin: 0,
          }}>
            {t('trip.my_trips_title')}
          </h1>
        </div>
      </header>

      {/* Contenu */}
      <div style={{
        flex: 1,
        overflowY: 'auto',
        WebkitOverflowScrolling: 'touch',
        padding: 'var(--space-4)',
        paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-20))',
      }}>
        {isLoading && <SkeletonCard count={3} />}
        {isError && <EmptyState message={t('error.offline_fetch')} />}

        {!isLoading && !isError && trips && (
          trips.length === 0 ? (
            <EmptyState message={t('trip.no_trips')} />
          ) : (
            <div
              role="list"
              data-testid="my-trips-list"
              aria-label={t('trip.my_trips_title')}
            >
              {trips.map(trip => (
                <TripCard key={trip.id} trip={trip} />
              ))}
            </div>
          )
        )}
      </div>

      {/* FAB Créer */}
      <button
        type="button"
        data-testid="create-trip-fab"
        onClick={() => navigate('/trips/new')}
        aria-label={t('trip.new_trip')}
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
          fontSize: '24px',
          cursor: 'pointer',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          boxShadow: 'var(--shadow-lg)',
          zIndex: 500,
        }}
      >
        <span aria-hidden="true">+</span>
      </button>
    </div>
  );
}
