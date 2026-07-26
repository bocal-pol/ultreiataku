import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import type { StageModel } from '../../../models/pilgrimage.ts';

const ACCOM_ICONS: Record<string, string> = {
  gite: '🏠',
  camping: '⛺',
  abbey: '⛪',
  hostel: '🛏',
  bivouac: '🌿',
  hotel: '🏨',
  donativo: '🤝',
};

interface StageCardProps {
  stage: StageModel;
}

function formatDuration(h: number): { hours: number; minutes: number } {
  const hours = Math.floor(h);
  const minutes = Math.round((h - hours) * 60);
  return { hours, minutes };
}

export function StageCard({ stage }: StageCardProps) {
  const { t } = useTranslation('pilgrimage');
  const navigate = useNavigate();
  const { hours, minutes } = formatDuration(stage.estimatedDurationH);

  const icon = ACCOM_ICONS[stage.accommodationTypeDefault] ?? '🏠';

  return (
    <article
      role="listitem"
      onClick={() => navigate(`/etapes/${stage.code}`)}
      onKeyDown={e => { if (e.key === 'Enter' || e.key === ' ') navigate(`/etapes/${stage.code}`); }}
      tabIndex={0}
      aria-label={`${t('stage.day_label', { n: stage.dayNumber })} ${stage.startWaypoint.name} vers ${stage.endWaypoint.name}, ${stage.distanceKm} kilomètres`}
      style={{
        backgroundColor: 'var(--stage-card-bg)',
        borderRadius: 'var(--stage-card-radius)',
        border: '1px solid var(--stage-card-border)',
        padding: 'var(--stage-card-padding)',
        display: 'flex',
        gap: '12px',
        alignItems: 'center',
        minHeight: '72px',
        cursor: 'pointer',
        transition: 'background-color var(--duration-fast) var(--easing-default)',
        WebkitTapHighlightColor: 'transparent',
        outline: 'none',
      }}
    >
      {/* Badge jour */}
      <div
        aria-hidden="true"
        style={{
          width: 'var(--stage-badge-size)',
          height: 'var(--stage-badge-size)',
          borderRadius: '50%',
          backgroundColor: 'var(--stage-badge-bg)',
          color: 'var(--stage-badge-text)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 'var(--font-size-xs)',
          fontWeight: 'var(--font-weight-semibold)',
          flexShrink: 0,
        }}
      >
        {t('stage.day_label', { n: stage.dayNumber })}
      </div>

      {/* Contenu */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-primary)',
          whiteSpace: 'nowrap',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
        }}>
          {stage.startWaypoint.name} → {stage.endWaypoint.name}
        </div>
        <div style={{
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-text-secondary)',
          marginTop: '2px',
        }}>
          {t('stage.distance', { x: stage.distanceKm })} ·{' '}
          {t('stage.elevation', { y: stage.elevationGainM })} ·{' '}
          {t('stage.duration', { h: hours, m: String(minutes).padStart(2, '0') })}
        </div>
        <div style={{
          display: 'flex',
          gap: '8px',
          marginTop: '4px',
          fontSize: 'var(--font-size-xs)',
          color: 'var(--color-text-tertiary)',
          flexWrap: 'wrap',
        }}>
          <span aria-hidden="true">{icon} {t(`accommodation.types.${stage.accommodationTypeDefault}`, { defaultValue: stage.accommodationTypeDefault })}</span>
        </div>
      </div>

      {/* Flèche */}
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" style={{ color: 'var(--color-text-tertiary)', flexShrink: 0 }}>
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    </article>
  );
}
