/**
 * TripJoinScreen — /trips/join/:token
 * Accepter ou décliner une invitation à rejoindre un voyage.
 * ULTREIA-37
 */

import { useParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../../context/useAuth.ts';
import { useTripJoinPreview, useJoinByToken } from '../../../shared/hooks/useTrips.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';

export function TripJoinScreen() {
  const { token } = useParams<{ token: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const { isAuthenticated, isLoading: authLoading, login } = useAuth();

  const joinMutation = useJoinByToken();

  // Preview du trip (chargé seulement si on a un token)
  const {
    data: preview,
    isLoading: previewLoading,
    isError: previewError,
  } = useTripJoinPreview(token ?? '');

  // Pendant le chargement de l'auth
  if (authLoading) {
    return (
      <div
        role="status"
        aria-live="polite"
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          color: 'var(--color-text-tertiary)',
          fontSize: 'var(--font-size-md)',
        }}
      >
        {t('auth.connecting')}
      </div>
    );
  }

  // Non authentifié : proposer le login, retour vers cette URL après
  if (!isAuthenticated) {
    return (
      <div
        data-testid="trip-join-screen"
        style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          padding: 'var(--space-8)',
          textAlign: 'center',
          gap: 'var(--space-6)',
          backgroundColor: 'var(--color-bg-base)',
        }}
      >
        <div>
          <p style={{
            fontSize: 'var(--font-size-lg)',
            fontFamily: 'var(--font-family-display)',
            color: 'var(--color-gold-500)',
            margin: '0 0 var(--space-2) 0',
          }}>
            {t('trip.join_preview_title')}
          </p>
          <p style={{
            fontSize: 'var(--font-size-sm)',
            color: 'var(--color-text-secondary)',
            margin: 0,
          }}>
            {t('auth.login_required')}
          </p>
        </div>

        <button
          type="button"
          onClick={() => login(`/trips/join/${token ?? ''}`)}
          style={{
            width: '100%',
            maxWidth: '320px',
            minHeight: '56px',
            backgroundColor: 'var(--color-interactive-primary)',
            color: 'var(--color-text-inverse)',
            border: 'none',
            borderRadius: 'var(--radius-lg)',
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

  // Token manquant
  if (!token) {
    return (
      <div
        data-testid="trip-join-screen"
        role="alert"
        style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          padding: 'var(--space-8)',
          textAlign: 'center',
          gap: 'var(--space-4)',
          backgroundColor: 'var(--color-bg-base)',
        }}
      >
        <p style={{ color: 'var(--color-text-secondary)', fontSize: 'var(--font-size-md)' }}>
          {t('error.invite_invalid')}
        </p>
        <button
          type="button"
          onClick={() => navigate('/belgique')}
          style={{
            minHeight: '44px',
            backgroundColor: 'var(--color-bg-elevated)',
            border: '1px solid var(--color-border-subtle)',
            borderRadius: 'var(--radius-lg)',
            padding: '0 var(--space-6)',
            color: 'var(--color-text-primary)',
            cursor: 'pointer',
          }}
        >
          {t('nav.stages')}
        </button>
      </div>
    );
  }

  if (previewLoading) {
    return <div style={{ padding: 'var(--space-4)' }}><SkeletonCard count={2} /></div>;
  }

  // Token invalide / expiré (404 ou 410)
  if (previewError || !preview) {
    return (
      <div
        data-testid="trip-join-screen"
        role="alert"
        style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          padding: 'var(--space-8)',
          textAlign: 'center',
          gap: 'var(--space-4)',
          backgroundColor: 'var(--color-bg-base)',
        }}
      >
        <p style={{
          fontSize: 'var(--font-size-md)',
          color: 'var(--color-text-secondary)',
        }}>
          {t('error.invite_invalid')}
        </p>
        <button
          type="button"
          onClick={() => navigate('/belgique')}
          style={{
            minHeight: '44px',
            backgroundColor: 'var(--color-bg-elevated)',
            border: '1px solid var(--color-border-subtle)',
            borderRadius: 'var(--radius-lg)',
            padding: '0 var(--space-6)',
            color: 'var(--color-text-primary)',
            cursor: 'pointer',
          }}
        >
          {t('nav.stages')}
        </button>
      </div>
    );
  }

  function handleAccept() {
    joinMutation.mutate(
      { token: token! },
      {
        onSuccess: (trip) => {
          navigate(`/trips/${trip.id}`, { replace: true });
        },
      },
    );
  }

  function handleDecline() {
    navigate('/belgique', { replace: true });
  }

  return (
    <div
      data-testid="trip-join-screen"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
      }}
    >
      {/* Header */}
      <header style={{
        position: 'sticky', top: 0, zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px' }}>
          <h1 style={{
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            color: 'var(--color-text-primary)',
            margin: 0,
            fontFamily: 'var(--font-family-interface)',
          }}>
            {t('trip.join_preview_title')}
          </h1>
        </div>
      </header>

      {/* Résumé du voyage */}
      <div style={{ flex: 1, overflowY: 'auto', padding: 'var(--space-4)', display: 'flex', flexDirection: 'column', gap: 'var(--space-4)' }}>

        {/* Carte voyage */}
        <div style={{
          backgroundColor: 'var(--color-bg-elevated)',
          borderRadius: 'var(--radius-lg)',
          border: '1px solid var(--color-border-subtle)',
          padding: 'var(--space-5)',
          display: 'flex',
          flexDirection: 'column',
          gap: 'var(--space-3)',
        }}>
          <div>
            <div style={{
              fontSize: 'var(--font-size-xs)',
              letterSpacing: 'var(--letter-spacing-wide)',
              textTransform: 'uppercase',
              color: 'var(--color-text-tertiary)',
              marginBottom: 'var(--space-1)',
            }}>
              {t('trip.my_trips_title')}
            </div>
            <div style={{
              fontSize: 'var(--font-size-xl)',
              fontWeight: 'var(--font-weight-semibold)',
              color: 'var(--color-text-primary)',
              fontFamily: 'var(--font-family-display)',
            }}>
              {preview.trip.name}
            </div>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-2)' }}>
            <div style={{ display: 'flex', gap: 'var(--space-2)', alignItems: 'center' }}>
              <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', minWidth: '80px' }}>
                {t('trip.route_label')}
              </span>
              <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
                {preview.trip.route.name}
              </span>
            </div>

            <div style={{ display: 'flex', gap: 'var(--space-2)', alignItems: 'center' }}>
              <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', minWidth: '80px' }}>
                Organisateur
              </span>
              <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
                {preview.trip.organizer.displayName}
              </span>
            </div>

            <div style={{ display: 'flex', gap: 'var(--space-2)', alignItems: 'center' }}>
              <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', minWidth: '80px' }}>
                {t('trip.members_section')}
              </span>
              <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
                {preview.trip.members.length} pèlerin{preview.trip.members.length > 1 ? 's' : ''}
              </span>
            </div>

            {preview.trip.estimatedStartDate && (
              <div style={{ display: 'flex', gap: 'var(--space-2)', alignItems: 'center' }}>
                <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', minWidth: '80px' }}>
                  {t('trip.start_date_label')}
                </span>
                <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
                  {preview.trip.estimatedStartDate}
                </span>
              </div>
            )}
          </div>

          {/* Rôle attribué */}
          <div style={{
            backgroundColor: 'rgba(200,150,60,0.06)',
            border: '1px solid rgba(200,150,60,0.2)',
            borderRadius: 'var(--radius-md)',
            padding: 'var(--space-3)',
            display: 'flex',
            alignItems: 'center',
            gap: 'var(--space-2)',
          }}>
            <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
              Votre rôle :
            </span>
            <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-gold-500)', fontWeight: 'var(--font-weight-medium)' }}>
              {t(`trip.roles.${preview.role}` as Parameters<typeof t>[0])}
            </span>
          </div>
        </div>

        {/* Erreur mutation */}
        {joinMutation.isError && (
          <div
            role="alert"
            style={{
              backgroundColor: 'rgba(232,80,58,0.08)',
              borderRadius: 'var(--radius-md)',
              border: '1px solid rgba(232,80,58,0.25)',
              padding: 'var(--space-3)',
              fontSize: 'var(--font-size-sm)',
              color: 'var(--color-error, #e8503a)',
            }}
          >
            {t('error.sync_failed')}
          </div>
        )}

        {/* Boutons d'action */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-3)', marginTop: 'var(--space-2)' }}>
          <button
            type="button"
            data-testid="join-accept-btn"
            onClick={handleAccept}
            disabled={joinMutation.isPending}
            aria-busy={joinMutation.isPending}
            style={{
              minHeight: '56px',
              backgroundColor: 'var(--color-interactive-primary)',
              color: 'var(--color-text-inverse)',
              border: 'none',
              borderRadius: 'var(--radius-lg)',
              fontSize: 'var(--font-size-md)',
              fontWeight: 'var(--font-weight-semibold)',
              cursor: joinMutation.isPending ? 'wait' : 'pointer',
              opacity: joinMutation.isPending ? 0.7 : 1,
            }}
          >
            {joinMutation.isPending ? '…' : t('trip.join_accept')}
          </button>

          <button
            type="button"
            data-testid="join-decline-btn"
            onClick={handleDecline}
            disabled={joinMutation.isPending}
            style={{
              minHeight: '48px',
              backgroundColor: 'transparent',
              color: 'var(--color-text-secondary)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-lg)',
              fontSize: 'var(--font-size-sm)',
              cursor: 'pointer',
            }}
          >
            {t('trip.join_decline')}
          </button>
        </div>
      </div>
    </div>
  );
}
