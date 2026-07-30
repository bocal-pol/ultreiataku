/**
 * ProfileScreen — /profil
 * Affiche le profil du pèlerin connecté (depuis /me) et la liste de ses voyages.
 * Remplace le placeholder "à venir" par un écran réel.
 *
 * FIX-PROFIL-001 : données issues de useAuth().currentUser (CurrentUserModel)
 * + useMyTrips() pour la liste des voyages — aucun DTO brut dans ce composant.
 *
 * QUICK-WIN-001 : ajout bouton "Quitter le Chemin" (logout), lien Administration
 * (VITE_ADMIN_URL) et lien "Préparer le Chemin" (guides).
 */

import { useTranslation } from 'react-i18next';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../../context/useAuth.ts';
import { useMyTrips } from '../../../shared/hooks/useTrips.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { TripModel } from '../../../models/pilgrimage.ts';

const ADMIN_URL = (import.meta.env['VITE_ADMIN_URL'] as string | undefined)
  ?? 'http://localhost:8096/admin';

// ─── Icônes ───────────────────────────────────────────────────────────────────

const ExternalLinkIcon = () => (
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
    <polyline points="15 3 21 3 21 9" />
    <line x1="10" y1="14" x2="21" y2="3" />
  </svg>
);

const ChevronRightIcon = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M9 18l6-6-6-6" />
  </svg>
);

// ─── Sous-composants ─────────────────────────────────────────────────────────

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div style={{
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      padding: 'var(--space-3) 0',
      borderBottom: '1px solid var(--color-border-subtle)',
    }}>
      <span style={{
        fontSize: 'var(--font-size-sm)',
        color: 'var(--color-text-tertiary)',
        fontFamily: 'var(--font-family-interface)',
      }}>
        {label}
      </span>
      <span style={{
        fontSize: 'var(--font-size-sm)',
        color: 'var(--color-text-primary)',
        textAlign: 'right',
        maxWidth: '60%',
        wordBreak: 'break-all',
      }}>
        {value}
      </span>
    </div>
  );
}

function TripSummaryRow({ trip, onClick }: { trip: TripModel; onClick: () => void }) {
  const { t } = useTranslation('pilgrimage');

  const statusColors: Record<TripModel['status'], string> = {
    planned: 'var(--color-text-tertiary)',
    active: 'var(--color-camp-green, #5a9e5a)',
    completed: 'var(--color-text-secondary)',
    cancelled: 'var(--color-error, #e8503a)',
  };

  return (
    <button
      type="button"
      onClick={onClick}
      style={{
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-1)',
        width: '100%',
        textAlign: 'left',
        background: 'none',
        border: 'none',
        padding: 'var(--space-3) 0',
        borderBottom: '1px solid var(--color-border-subtle)',
        cursor: 'pointer',
        minHeight: '44px',
      }}
    >
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        gap: 'var(--space-2)',
      }}>
        <span style={{
          fontSize: 'var(--font-size-sm)',
          fontWeight: 'var(--font-weight-medium)',
          color: 'var(--color-text-primary)',
        }}>
          {trip.name}
        </span>
        <span style={{
          fontSize: 'var(--font-size-xs)',
          color: statusColors[trip.status],
          flexShrink: 0,
        }}>
          {t(`trip.status.${trip.status}` as Parameters<typeof t>[0])}
        </span>
      </div>
      <span style={{
        fontSize: 'var(--font-size-xs)',
        color: 'var(--color-text-tertiary)',
      }}>
        {trip.route.name}
        {trip.estimatedStartDate ? ` · ${trip.estimatedStartDate}` : ''}
      </span>
    </button>
  );
}

// ─── Écran principal ─────────────────────────────────────────────────────────

export function ProfileScreen() {
  const { t } = useTranslation('pilgrimage');
  const navigate = useNavigate();
  const { currentUser, isAuthenticated, isLoading: authLoading, login, logout } = useAuth();

  // useMyTrips retourne [] si non authentifié ou si 401 (retry=false pour 401)
  const { data: trips, isLoading: tripsLoading } = useMyTrips();

  // Non connecté
  if (!authLoading && !isAuthenticated) {
    return (
      <div
        data-testid="profile-screen"
        style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          gap: 'var(--space-6)',
          padding: 'var(--space-8)',
          backgroundColor: 'var(--color-bg-base)',
        }}
      >
        <p style={{
          fontSize: 'var(--font-size-md)',
          color: 'var(--color-text-secondary)',
          textAlign: 'center',
          margin: 0,
        }}>
          {t('auth.login_required')}
        </p>
        <button
          type="button"
          onClick={() => login('/profil')}
          style={{
            minHeight: '56px',
            backgroundColor: 'var(--color-interactive-primary)',
            color: 'var(--color-text-inverse)',
            border: 'none',
            borderRadius: 'var(--radius-lg)',
            padding: '0 var(--space-8)',
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            cursor: 'pointer',
          }}
        >
          {t('auth.login_cta')}
        </button>
      </div>
    );
  }

  return (
    <div
      data-testid="profile-screen"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
      }}
    >
      {/* Header sticky */}
      <header style={{
        position: 'sticky',
        top: 0,
        zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px' }}>
          <h1 style={{
            margin: 0,
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            color: 'var(--color-text-primary)',
            fontFamily: 'var(--font-family-interface)',
          }}>
            {t('nav.profile')}
          </h1>
        </div>
      </header>

      {/* Contenu scrollable */}
      <div style={{
        flex: 1,
        overflowY: 'auto',
        WebkitOverflowScrolling: 'touch',
        padding: 'var(--space-4)',
        paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-8))',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-6)',
      }}>
        {authLoading && <SkeletonCard count={2} />}

        {/* Section identité pèlerin */}
        {!authLoading && currentUser && (
          <section aria-labelledby="profile-identity-heading">
            <h2
              id="profile-identity-heading"
              style={{
                fontSize: 'var(--font-size-xs)',
                fontWeight: 'var(--font-weight-semibold)',
                letterSpacing: 'var(--letter-spacing-wide)',
                textTransform: 'uppercase',
                color: 'var(--color-text-tertiary)',
                fontFamily: 'var(--font-family-interface)',
                margin: '0 0 var(--space-2) 0',
              }}
            >
              {t('nav.profile')}
            </h2>

            <div style={{
              backgroundColor: 'var(--color-bg-elevated)',
              borderRadius: 'var(--radius-lg)',
              border: '1px solid var(--color-border-subtle)',
              padding: '0 var(--space-4)',
            }}>
              <InfoRow label="Nom" value={currentUser.pilgrim.displayName} />
              <InfoRow label="E-mail" value={currentUser.email} />
              <InfoRow
                label="Configuration"
                value={currentUser.pilgrim.configuration}
              />
              <InfoRow
                label="Langue"
                value={currentUser.pilgrim.preferredLocale.toUpperCase()}
              />
            </div>
          </section>
        )}

        {/* Section voyages */}
        {!authLoading && currentUser && (
          <section aria-labelledby="profile-trips-heading">
            <h2
              id="profile-trips-heading"
              style={{
                fontSize: 'var(--font-size-xs)',
                fontWeight: 'var(--font-weight-semibold)',
                letterSpacing: 'var(--letter-spacing-wide)',
                textTransform: 'uppercase',
                color: 'var(--color-text-tertiary)',
                fontFamily: 'var(--font-family-interface)',
                margin: '0 0 var(--space-2) 0',
              }}
            >
              {t('trip.my_trips_title')}
            </h2>

            {tripsLoading && <SkeletonCard count={2} />}

            {!tripsLoading && trips && trips.length === 0 && (
              <EmptyState message={t('trip.no_trips')} />
            )}

            {!tripsLoading && trips && trips.length > 0 && (
              <div style={{
                backgroundColor: 'var(--color-bg-elevated)',
                borderRadius: 'var(--radius-lg)',
                border: '1px solid var(--color-border-subtle)',
                padding: '0 var(--space-4)',
              }}>
                {trips.map(trip => (
                  <TripSummaryRow
                    key={trip.id}
                    trip={trip}
                    onClick={() => navigate(`/trips/${trip.id}`)}
                  />
                ))}
              </div>
            )}

            <button
              type="button"
              data-testid="profile-new-trip-cta"
              onClick={() => navigate('/trips/new')}
              style={{
                marginTop: 'var(--space-3)',
                width: '100%',
                minHeight: '44px',
                backgroundColor: 'transparent',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-lg)',
                color: 'var(--color-text-accent)',
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-medium)',
                cursor: 'pointer',
              }}
            >
              {t('trip.new_trip')}
            </button>
          </section>
        )}

        {/* Section Paramètres — toujours visible si connecté */}
        {!authLoading && isAuthenticated && (
          <section aria-labelledby="profile-settings-heading">
            <h2
              id="profile-settings-heading"
              style={{
                fontSize: 'var(--font-size-xs)',
                fontWeight: 'var(--font-weight-semibold)',
                letterSpacing: 'var(--letter-spacing-wide)',
                textTransform: 'uppercase',
                color: 'var(--color-text-tertiary)',
                fontFamily: 'var(--font-family-interface)',
                margin: '0 0 var(--space-2) 0',
              }}
            >
              {t('profile.settings_section')}
            </h2>

            <div style={{
              backgroundColor: 'var(--color-bg-elevated)',
              borderRadius: 'var(--radius-lg)',
              border: '1px solid var(--color-border-subtle)',
              overflow: 'hidden',
            }}>
              {/* Lien Guides du Chemin */}
              <Link
                to="/guides"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                  padding: 'var(--space-3) var(--space-4)',
                  borderBottom: '1px solid var(--color-border-subtle)',
                  textDecoration: 'none',
                  color: 'var(--color-text-primary)',
                  fontSize: 'var(--font-size-sm)',
                  minHeight: '48px',
                  WebkitTapHighlightColor: 'transparent',
                }}
              >
                <span>{t('profile.guides_link')}</span>
                <ChevronRightIcon />
              </Link>

              {/* Lien Administration (Filament) — nouvel onglet */}
              <a
                href={ADMIN_URL}
                target="_blank"
                rel="noopener noreferrer"
                data-testid="profile-admin-link"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                  padding: 'var(--space-3) var(--space-4)',
                  borderBottom: '1px solid var(--color-border-subtle)',
                  textDecoration: 'none',
                  color: 'var(--color-text-primary)',
                  fontSize: 'var(--font-size-sm)',
                  minHeight: '48px',
                  WebkitTapHighlightColor: 'transparent',
                }}
              >
                <span>{t('profile.admin_link')}</span>
                <ExternalLinkIcon />
              </a>

              {/* Bouton Quitter le Chemin (logout) */}
              <button
                type="button"
                data-testid="profile-logout-btn"
                onClick={() => logout()}
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  width: '100%',
                  textAlign: 'left',
                  background: 'none',
                  border: 'none',
                  padding: 'var(--space-3) var(--space-4)',
                  color: 'var(--color-error, #e8503a)',
                  fontSize: 'var(--font-size-sm)',
                  fontWeight: 'var(--font-weight-medium)',
                  cursor: 'pointer',
                  minHeight: '48px',
                  fontFamily: 'var(--font-family-interface)',
                  WebkitTapHighlightColor: 'transparent',
                }}
              >
                {t('auth.logout_cta')}
              </button>
            </div>
          </section>
        )}
      </div>
    </div>
  );
}
