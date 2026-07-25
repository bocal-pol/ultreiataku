import { useTranslation } from 'react-i18next';

type DetourType = 'on_path' | 'short' | 'medium' | 'long';

const DETOUR_STYLES: Record<DetourType, { bg: string; color: string }> = {
  on_path: { bg: 'rgba(90,158,90,0.15)', color: 'var(--color-camp-green)' },
  short:   { bg: 'rgba(74,144,217,0.15)', color: 'var(--color-trail-blue)' },
  medium:  { bg: 'rgba(232,152,58,0.15)', color: 'var(--color-detour-amber)' },
  long:    { bg: 'rgba(192,64,64,0.15)', color: 'var(--color-error-500)' },
};

interface DetourBadgeProps {
  type: DetourType;
}

export function DetourBadge({ type }: DetourBadgeProps) {
  const { t } = useTranslation('pilgrimage');
  const style = DETOUR_STYLES[type];

  return (
    <span
      style={{
        display: 'inline-block',
        backgroundColor: style.bg,
        color: style.color,
        borderRadius: 'var(--radius-full)',
        padding: '2px 8px',
        fontSize: 'var(--font-size-xs)',
        fontWeight: 'var(--font-weight-medium)',
      }}
    >
      {t(`poi.detour_types.${type}`)}
    </span>
  );
}
