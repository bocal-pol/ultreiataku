/**
 * TripDashboardScreen — /trips/:id
 * Tableau de bord d'un voyage partagé.
 * ULTREIA-36 + ULTREIA-37 (DepartureForm, InviteDialog)
 */

import { useState, useId } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import {
  useTripDetail,
  useGenerateInviteToken,
  useRevokeInviteToken,
  useAddDeparture,
} from '../../../shared/hooks/useTrips.ts';
import { useAuth } from '../../../context/AuthContext.tsx';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { TripMemberModel } from '../../../models/pilgrimage.ts';
import { WhoCarriesWhat } from '../pack/WhoCarriesWhat.tsx';

// ─── Sous-composants ─────────────────────────────────────────────────────────

function SectionHeader({ title }: { title: string }) {
  return (
    <h2 style={{
      fontSize: 'var(--font-size-xs)',
      fontWeight: 'var(--font-weight-semibold)',
      letterSpacing: 'var(--letter-spacing-wide)',
      textTransform: 'uppercase',
      color: 'var(--color-text-tertiary)',
      fontFamily: 'var(--font-family-interface)',
      margin: '0 0 var(--space-3) 0',
    }}>
      {title}
    </h2>
  );
}

function AvatarInitials({ name }: { name: string }) {
  const initials = name
    .split(' ')
    .slice(0, 2)
    .map(w => w[0]?.toUpperCase() ?? '')
    .join('');

  return (
    <div
      aria-hidden="true"
      style={{
        width: '36px',
        height: '36px',
        borderRadius: '50%',
        backgroundColor: 'var(--color-gold-500)',
        color: 'var(--color-bg-base)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: 'var(--font-size-sm)',
        fontWeight: 'var(--font-weight-semibold)',
        flexShrink: 0,
      }}
    >
      {initials}
    </div>
  );
}

function MemberRow({ member }: { member: TripMemberModel }) {
  const { t } = useTranslation('pilgrimage');
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      gap: 'var(--space-3)',
      padding: 'var(--space-3) 0',
      borderBottom: '1px solid var(--color-border-subtle)',
      minHeight: '56px',
    }}>
      <AvatarInitials name={member.pilgrim.displayName} />
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 'var(--font-size-md)', color: 'var(--color-text-primary)', fontWeight: 'var(--font-weight-medium)' }}>
          {member.pilgrim.displayName}
        </div>
        <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
          {t(`trip.roles.${member.role}` as Parameters<typeof t>[0])}
        </div>
      </div>
    </div>
  );
}

// ─── InviteDialog ────────────────────────────────────────────────────────────

interface InviteDialogProps {
  tripId: string;
  currentToken: string | null;
  onClose: () => void;
}

function InviteDialog({ tripId, currentToken, onClose }: InviteDialogProps) {
  const { t } = useTranslation('pilgrimage');
  const dialogTitleId = useId();
  const generateMutation = useGenerateInviteToken(tripId);
  const revokeMutation = useRevokeInviteToken(tripId);
  const [copied, setCopied] = useState(false);

  const token = generateMutation.data ?? currentToken;
  const inviteUrl = token ? `${window.location.origin}/trips/join/${token}` : null;

  function handleGenerate() {
    generateMutation.mutate();
    setCopied(false);
  }

  function handleCopy() {
    if (!inviteUrl) return;
    navigator.clipboard.writeText(inviteUrl).then(() => {
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }).catch(() => {
      // Fallback clipboard
      const ta = document.createElement('textarea');
      ta.value = inviteUrl;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    });
  }

  function handleRevoke() {
    revokeMutation.mutate(undefined, {
      onSuccess: () => {
        generateMutation.reset();
        setCopied(false);
      },
    });
  }

  return (
    <>
      {/* Backdrop */}
      <div
        aria-hidden="true"
        onClick={onClose}
        style={{
          position: 'fixed',
          inset: 0,
          backgroundColor: 'var(--sheet-backdrop, rgba(0,0,0,0.5))',
          zIndex: 1050,
        }}
      />

      {/* Dialog */}
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby={dialogTitleId}
        data-testid="invite-dialog"
        style={{
          position: 'fixed',
          bottom: 0,
          left: 0,
          right: 0,
          backgroundColor: 'var(--color-bg-elevated)',
          borderRadius: 'var(--radius-xl) var(--radius-xl) 0 0',
          padding: 'var(--space-6)',
          zIndex: 1100,
          maxHeight: '70vh',
          overflowY: 'auto',
          display: 'flex',
          flexDirection: 'column',
          gap: 'var(--space-4)',
        }}
      >
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <h3 id={dialogTitleId} style={{ margin: 0, fontSize: 'var(--font-size-lg)', color: 'var(--color-text-primary)' }}>
            {t('trip.invite_dialog_title')}
          </h3>
          <button
            type="button"
            onClick={onClose}
            aria-label={t('journal.cancel')}
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              color: 'var(--color-text-tertiary)', minWidth: '44px', minHeight: '44px',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              borderRadius: 'var(--radius-md)', fontSize: '20px',
            }}
          >
            ✕
          </button>
        </div>

        {/* Générer ou afficher le token */}
        {!token ? (
          <button
            type="button"
            data-testid="invite-btn"
            onClick={handleGenerate}
            disabled={generateMutation.isPending}
            style={{
              minHeight: '56px',
              backgroundColor: 'var(--color-interactive-primary)',
              color: 'var(--color-text-inverse)',
              border: 'none',
              borderRadius: 'var(--radius-lg)',
              fontSize: 'var(--font-size-md)',
              fontWeight: 'var(--font-weight-semibold)',
              cursor: generateMutation.isPending ? 'wait' : 'pointer',
            }}
          >
            {generateMutation.isPending ? '…' : t('trip.invite_cta')}
          </button>
        ) : (
          <>
            <div>
              <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginBottom: 'var(--space-2)' }}>
                {t('trip.invite_link_label')}
              </div>
              <div
                data-testid="invite-token-display"
                style={{
                  backgroundColor: 'var(--color-bg-base)',
                  borderRadius: 'var(--radius-md)',
                  border: '1px solid var(--color-border-subtle)',
                  padding: 'var(--space-3)',
                  fontSize: 'var(--font-size-sm)',
                  color: 'var(--color-text-secondary)',
                  wordBreak: 'break-all',
                  fontFamily: 'monospace',
                }}
              >
                {inviteUrl}
              </div>
            </div>

            <div style={{ display: 'flex', gap: 'var(--space-3)' }}>
              <button
                type="button"
                data-testid="invite-copy-btn"
                onClick={handleCopy}
                style={{
                  flex: 1,
                  minHeight: '48px',
                  backgroundColor: 'var(--color-interactive-primary)',
                  color: 'var(--color-text-inverse)',
                  border: 'none',
                  borderRadius: 'var(--radius-lg)',
                  fontSize: 'var(--font-size-sm)',
                  fontWeight: 'var(--font-weight-semibold)',
                  cursor: 'pointer',
                }}
              >
                {copied ? t('trip.invite_copied') : t('trip.invite_copy')}
              </button>
              <button
                type="button"
                data-testid="invite-revoke-btn"
                onClick={handleRevoke}
                disabled={revokeMutation.isPending}
                style={{
                  minHeight: '48px',
                  backgroundColor: 'transparent',
                  color: 'var(--color-detour-amber)',
                  border: '1px solid var(--color-detour-amber)',
                  borderRadius: 'var(--radius-lg)',
                  fontSize: 'var(--font-size-sm)',
                  cursor: revokeMutation.isPending ? 'wait' : 'pointer',
                  padding: '0 var(--space-4)',
                }}
              >
                {t('trip.invite_revoke')}
              </button>
            </div>
          </>
        )}
      </div>
    </>
  );
}

// ─── DepartureForm ───────────────────────────────────────────────────────────

interface DepartureFormProps {
  tripId: string;
  stages: Array<{ id: string; code: string; name: string; dayNumber: number }>;
}

function DepartureForm({ tripId, stages }: DepartureFormProps) {
  const { t } = useTranslation('pilgrimage');
  const addMutation = useAddDeparture(tripId);

  const [startStageId, setStartStageId] = useState('');
  const [endStageId, setEndStageId] = useState('');
  const [departureDate, setDepartureDate] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const startStage = stages.find(s => s.id === startStageId);
  const filteredEndStages = startStage
    ? stages.filter(s => s.dayNumber >= startStage.dayNumber)
    : stages;

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    if (!startStageId || !endStageId || !departureDate) {
      setError('Tous les champs sont requis.');
      return;
    }

    const startStageData = stages.find(s => s.id === startStageId);
    const endStageData = stages.find(s => s.id === endStageId);

    if (startStageData && endStageData && endStageData.dayNumber < startStageData.dayNumber) {
      setError(t('trip.departure_error_dates'));
      return;
    }

    addMutation.mutate(
      { start_stage_id: startStageId, end_stage_id: endStageId, planned_start_date: departureDate },
      {
        onSuccess: () => {
          setSuccess(true);
          setStartStageId('');
          setEndStageId('');
          setDepartureDate('');
        },
        onError: () => setError(t('error.sync_failed')),
      },
    );
  }

  return (
    <form
      data-testid="departure-form"
      onSubmit={handleSubmit}
      noValidate
      style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}
    >
      {error && (
        <div
          role="alert"
          data-testid="departure-error"
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

      {success && (
        <div
          role="status"
          style={{
            fontSize: 'var(--font-size-sm)',
            color: 'var(--color-camp-green)',
            backgroundColor: 'rgba(90,158,90,0.08)',
            padding: 'var(--space-2) var(--space-3)',
            borderRadius: 'var(--radius-md)',
          }}
        >
          {t('success.entry_saved')}
        </div>
      )}

      {/* Date de départ */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
        <label
          htmlFor="departure-date"
          style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}
        >
          {t('trip.departure_date')}
        </label>
        <input
          id="departure-date"
          type="date"
          data-testid="departure-date"
          value={departureDate}
          onChange={(e) => setDepartureDate(e.target.value)}
          required
          aria-required="true"
          style={{
            minHeight: '44px',
            backgroundColor: 'var(--color-bg-base)',
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

      {/* Étape de départ */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
        <label
          htmlFor="departure-start-stage"
          style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}
        >
          {t('trip.departure_start')}
        </label>
        <select
          id="departure-start-stage"
          data-testid="departure-start-stage"
          value={startStageId}
          onChange={(e) => {
            setStartStageId(e.target.value);
            setEndStageId(''); // reset end si start change
          }}
          required
          aria-required="true"
          style={{
            minHeight: '44px',
            backgroundColor: 'var(--color-bg-base)',
            border: '1px solid var(--color-border-subtle)',
            borderRadius: 'var(--radius-md)',
            padding: '0 var(--space-3)',
            fontSize: 'var(--font-size-sm)',
            color: startStageId ? 'var(--color-text-primary)' : 'var(--color-text-tertiary)',
            boxSizing: 'border-box',
            width: '100%',
          }}
        >
          <option value="" disabled>{t('trip.departure_start')}</option>
          {stages.map(s => (
            <option key={s.id} value={s.id}>{s.name}</option>
          ))}
        </select>
      </div>

      {/* Étape d'arrivée */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
        <label
          htmlFor="departure-end-stage"
          style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}
        >
          {t('trip.departure_end')}
        </label>
        <select
          id="departure-end-stage"
          data-testid="departure-end-stage"
          value={endStageId}
          onChange={(e) => setEndStageId(e.target.value)}
          required
          aria-required="true"
          style={{
            minHeight: '44px',
            backgroundColor: 'var(--color-bg-base)',
            border: '1px solid var(--color-border-subtle)',
            borderRadius: 'var(--radius-md)',
            padding: '0 var(--space-3)',
            fontSize: 'var(--font-size-sm)',
            color: endStageId ? 'var(--color-text-primary)' : 'var(--color-text-tertiary)',
            boxSizing: 'border-box',
            width: '100%',
          }}
        >
          <option value="" disabled>{t('trip.departure_end')}</option>
          {filteredEndStages.map(s => (
            <option key={s.id} value={s.id}>{s.name}</option>
          ))}
        </select>
      </div>

      <button
        type="submit"
        data-testid="departure-submit"
        disabled={addMutation.isPending}
        aria-busy={addMutation.isPending}
        style={{
          minHeight: '48px',
          backgroundColor: 'var(--color-interactive-primary)',
          color: 'var(--color-text-inverse)',
          border: 'none',
          borderRadius: 'var(--radius-lg)',
          fontSize: 'var(--font-size-sm)',
          fontWeight: 'var(--font-weight-semibold)',
          cursor: addMutation.isPending ? 'wait' : 'pointer',
          opacity: addMutation.isPending ? 0.7 : 1,
        }}
      >
        {addMutation.isPending ? '…' : t('trip.departure_save')}
      </button>
    </form>
  );
}

// ─── Écran principal ─────────────────────────────────────────────────────────

export function TripDashboardScreen() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const { currentUser } = useAuth();

  const { data: trip, isLoading, isError } = useTripDetail(id ?? '');
  const [showInviteDialog, setShowInviteDialog] = useState(false);

  if (isLoading) {
    return <div style={{ padding: 'var(--space-4)' }}><SkeletonCard count={3} /></div>;
  }

  if (isError || !trip) {
    return <EmptyState message={t('error.stage_not_found')} />;
  }

  // Détecter mon rôle
  const myMember = currentUser
    ? trip.members.find(m => m.pilgrim.userId === currentUser.userId)
    : null;
  const isOrganizer = myMember?.role === 'organizer';
  const isParticipant = myMember?.role === 'participant';

  // Progression — calculée à partir des departures (simplifié V1)
  const totalStages = trip.route.stages.length;

  // Stages de la route pour DepartureForm
  const routeStages = trip.route.stages.map(s => ({
    id: s.id,
    code: s.code,
    name: s.name,
    dayNumber: s.dayNumber,
  }));

  return (
    <div
      data-testid="trip-dashboard"
      style={{ display: 'flex', flexDirection: 'column', height: '100%', backgroundColor: 'var(--color-bg-base)' }}
    >
      {/* Header */}
      <header style={{
        position: 'sticky', top: 0, zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', gap: 'var(--space-3)' }}>
          <button
            type="button"
            onClick={() => navigate('/trips')}
            aria-label="Mes voyages"
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              color: 'var(--color-text-accent)', padding: '8px', borderRadius: 'var(--radius-md)',
              display: 'flex', alignItems: 'center', minWidth: '44px', minHeight: '44px', justifyContent: 'center',
            }}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={{
              fontSize: 'var(--font-size-md)',
              fontWeight: 'var(--font-weight-semibold)',
              color: 'var(--color-text-primary)',
              fontFamily: 'var(--font-family-interface)',
              whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis',
            }}>
              {trip.name}
            </div>
            <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
              {trip.route.name} · {t(`trip.status.${trip.status}` as Parameters<typeof t>[0])}
            </div>
          </div>
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
        {/* Progression */}
        <section aria-labelledby="progress-heading">
          <SectionHeader title={t('trip.progress_section')} />
          <div id="progress-heading" style={{ display: 'none' }}>{t('trip.progress_section')}</div>
          <div style={{
            backgroundColor: 'var(--color-bg-elevated)',
            borderRadius: 'var(--radius-lg)',
            border: '1px solid var(--color-border-subtle)',
            padding: 'var(--space-4)',
          }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 'var(--space-2)' }}>
              <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
                {t('trip.progress', { current: 0, total: totalStages, km_done: 0, km_total: trip.route.totalDistanceKm })}
              </span>
            </div>
            <div
              role="progressbar"
              aria-valuemin={0}
              aria-valuemax={totalStages}
              aria-valuenow={0}
              aria-label={t('trip.progress_section')}
              style={{
                height: '6px',
                backgroundColor: 'var(--color-border-subtle)',
                borderRadius: 'var(--radius-full)',
                overflow: 'hidden',
              }}
            >
              <div style={{
                height: '100%',
                width: '0%',
                backgroundColor: 'var(--color-gold-500)',
                borderRadius: 'var(--radius-full)',
                transition: 'width 0.3s ease',
              }} />
            </div>
          </div>
        </section>

        {/* Membres */}
        <section aria-labelledby="members-heading">
          <SectionHeader title={t('trip.members_section')} />
          <div id="members-heading" style={{ display: 'none' }}>{t('trip.members_section')}</div>
          <div
            data-testid="members-list"
            role="list"
            aria-label={t('trip.members_section')}
          >
            {trip.members.map((member) => (
              <div key={member.pilgrim.id} role="listitem">
                <MemberRow member={member} />
              </div>
            ))}
          </div>

          {/* Bouton Inviter — organisateur uniquement */}
          {isOrganizer && (
            <button
              type="button"
              data-testid="invite-btn"
              onClick={() => setShowInviteDialog(true)}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: 'var(--space-2)',
                marginTop: 'var(--space-3)',
                background: 'none',
                border: '1px dashed var(--color-border-subtle)',
                borderRadius: 'var(--radius-lg)',
                padding: 'var(--space-3) var(--space-4)',
                cursor: 'pointer',
                color: 'var(--color-text-accent)',
                fontSize: 'var(--font-size-sm)',
                minHeight: '44px',
                width: '100%',
              }}
            >
              <span aria-hidden="true">+</span>
              {t('trip.invite_cta')}
            </button>
          )}
        </section>

        {/* Mon départ — organizer ou participant */}
        {(isOrganizer || isParticipant) && routeStages.length > 0 && (
          <section aria-labelledby="departure-heading">
            <SectionHeader title={t('trip.departure_section')} />
            <div id="departure-heading" style={{ display: 'none' }}>{t('trip.departure_section')}</div>
            <div style={{
              backgroundColor: 'var(--color-bg-elevated)',
              borderRadius: 'var(--radius-lg)',
              border: '1px solid var(--color-border-subtle)',
              padding: 'var(--space-4)',
            }}>
              <DepartureForm tripId={trip.id} stages={routeStages} />
            </div>
          </section>
        )}

        {/* Qui porte quoi — ULTREIA-45 */}
        {(isOrganizer || isParticipant) && (
          <section aria-labelledby="who-carries-heading">
            <SectionHeader title="Qui porte quoi" />
            <div id="who-carries-heading" style={{ display: 'none' }}>Qui porte quoi</div>
            <WhoCarriesWhat trip={trip} />
          </section>
        )}

        {/* Dernière entrée publique */}
        <section aria-labelledby="journal-heading">
          <SectionHeader title={t('trip.last_entry')} />
          <div id="journal-heading" style={{ display: 'none' }}>{t('trip.last_entry')}</div>
          <div style={{
            backgroundColor: 'var(--color-bg-elevated)',
            borderRadius: 'var(--radius-lg)',
            border: '1px solid var(--color-border-subtle)',
            padding: 'var(--space-4)',
          }}>
            <p style={{ margin: 0, fontSize: 'var(--font-size-sm)', color: 'var(--color-text-tertiary)' }}>
              {t('trip.no_public_entry')}
            </p>
          </div>
        </section>

        {/* Actions rapides */}
        <section aria-label="Actions rapides">
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 'var(--space-3)' }}>
            <button
              type="button"
              onClick={() => navigate(`/carte/${trip.route.stages[0]?.code ?? ''}`)}
              style={{
                minHeight: '56px',
                backgroundColor: 'var(--color-bg-elevated)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-lg)',
                color: 'var(--color-text-primary)',
                fontSize: 'var(--font-size-sm)',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 'var(--space-2)',
              }}
            >
              <span aria-hidden="true">🗺</span>
              {t('nav.map')}
            </button>
            <button
              type="button"
              onClick={() => navigate(`/journal/${trip.id}`)}
              style={{
                minHeight: '56px',
                backgroundColor: 'var(--color-bg-elevated)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-lg)',
                color: 'var(--color-text-primary)',
                fontSize: 'var(--font-size-sm)',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 'var(--space-2)',
              }}
            >
              <span aria-hidden="true">📓</span>
              {t('trip.journal_cta')}
            </button>
          </div>
        </section>
      </div>

      {/* Dialog invitation */}
      {showInviteDialog && (
        <InviteDialog
          tripId={trip.id}
          currentToken={trip.inviteToken}
          onClose={() => setShowInviteDialog(false)}
        />
      )}
    </div>
  );
}
