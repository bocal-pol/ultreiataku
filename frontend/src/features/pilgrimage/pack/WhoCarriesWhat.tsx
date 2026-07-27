/**
 * WhoCarriesWhat — Section "Qui porte quoi" sur le TripDashboard
 * ULTREIA-45 — Vague 1d (V1 : select étape simple, pas de drag-and-drop)
 *
 * NOTE TECHNIQUE (Phase C) :
 * L'API backend n'expose pas de endpoint GET pour lire les ItemAssignments
 * par departure. Seul POST /departures/{id}/assignments est disponible
 * (voir backend/app/Modules/Pilgrimage/Routes/api.php).
 * La liste des assignations réelles est dégradée proprement avec un message
 * informatif jusqu'à ce que GET /departures/{id}/assignments soit ajouté
 * côté backend (ticket ULTREIA-45b à créer).
 */

import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { TripModel } from '../../../models/pilgrimage.ts';

interface WhoCarriesWhatProps {
  trip: TripModel;
}

export function WhoCarriesWhat({ trip }: WhoCarriesWhatProps) {
  const { t } = useTranslation('pilgrimage');
  const [selectedStageId, setSelectedStageId] = useState<string>('');

  const stages = trip.route.stages;
  const members = trip.members.filter(m => m.role !== 'observer');

  const selectId = `who-carries-stage-${trip.id}`;

  return (
    <div
      data-testid="who-carries-what"
      style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid var(--color-border-subtle)',
        padding: 'var(--space-4)',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-3)',
      }}
    >
      {/* Sélecteur d'étape */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
        <label
          htmlFor={selectId}
          style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}
        >
          {t('trip.departure_start')}
        </label>
        <select
          id={selectId}
          value={selectedStageId}
          onChange={e => setSelectedStageId(e.target.value)}
          aria-label={t('trip.departure_start')}
          style={{
            minHeight: '44px',
            backgroundColor: 'var(--color-bg-base)',
            border: '1px solid var(--color-border-subtle)',
            borderRadius: 'var(--radius-md)',
            padding: '0 var(--space-3)',
            fontSize: 'var(--font-size-sm)',
            color: selectedStageId ? 'var(--color-text-primary)' : 'var(--color-text-tertiary)',
            boxSizing: 'border-box',
            width: '100%',
          }}
        >
          <option value="">{t('trip.departure_start')}</option>
          {stages.map(s => (
            <option key={s.id} value={s.id}>
              J{s.dayNumber} — {s.name}
            </option>
          ))}
        </select>
      </div>

      {/* Membres avec note de dégradation */}
      {selectedStageId && members.length > 0 && (
        <>
          {/* Bandeau informatif : endpoint GET non disponible */}
          <div
            role="status"
            aria-live="polite"
            data-testid="assignments-degraded-notice"
            style={{
              fontSize: 'var(--font-size-xs)',
              color: 'var(--color-detour-amber, #d4840a)',
              backgroundColor: 'rgba(212,132,10,0.08)',
              borderRadius: 'var(--radius-md)',
              padding: 'var(--space-2) var(--space-3)',
            }}
          >
            {t('pack.assignments_unavailable')}
          </div>

          <div
            role="list"
            aria-label="Répartition du sac"
            style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-2)' }}
          >
            {members.map(member => (
              <div
                key={member.pilgrim.id}
                role="listitem"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: 'var(--space-3)',
                  padding: 'var(--space-2) 0',
                  borderBottom: '1px solid var(--color-border-subtle)',
                }}
              >
                <div style={{
                  width: '32px',
                  height: '32px',
                  borderRadius: '50%',
                  backgroundColor: 'var(--color-gold-500)',
                  color: 'var(--color-bg-base)',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 'var(--font-size-xs)',
                  fontWeight: 'var(--font-weight-semibold)',
                  flexShrink: 0,
                }}
                  aria-hidden="true"
                >
                  {member.pilgrim.displayName.slice(0, 2).toUpperCase()}
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-primary)' }}>
                    {member.pilgrim.displayName}
                  </div>
                  <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
                    {t(`trip.roles.${member.role}` as Parameters<typeof t>[0])}
                  </div>
                </div>
                {/* Assignations non disponibles — endpoint GET /departures/{id}/assignments manquant (ULTREIA-45b) */}
                <div style={{
                  fontSize: 'var(--font-size-xs)',
                  color: 'var(--color-text-tertiary)',
                  fontStyle: 'italic',
                }}>
                  —
                </div>
              </div>
            ))}
          </div>
        </>
      )}

      {members.length === 0 && (
        <p style={{ margin: 0, fontSize: 'var(--font-size-sm)', color: 'var(--color-text-tertiary)' }}>
          {t('trip.members_section')} : —
        </p>
      )}
    </div>
  );
}
