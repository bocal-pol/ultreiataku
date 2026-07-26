import { useTranslation } from 'react-i18next';
import { useNetworkStatus } from '../hooks/useNetworkStatus.ts';

interface SyncIndicatorProps {
  pendingCount?: number;
}

export function SyncIndicator({ pendingCount = 0 }: SyncIndicatorProps) {
  const { t } = useTranslation('pilgrimage');
  const status = useNetworkStatus();

  if (status === 'online' && pendingCount === 0) return null;

  return (
    <div
      role="status"
      aria-live="polite"
      style={{
        position: 'fixed',
        top: '8px',
        left: '50%',
        transform: 'translateX(-50%)',
        zIndex: 9999,
        display: 'flex',
        alignItems: 'center',
        gap: '6px',
        padding: '2px 12px',
        borderRadius: 'var(--radius-full)',
        fontSize: 'var(--font-size-xs)',
        fontWeight: 'var(--font-weight-medium)',
        backgroundColor: 'var(--color-bg-elevated)',
        border: '1px solid var(--color-border-subtle)',
        boxShadow: 'var(--shadow-md)',
        color: status === 'offline' ? 'var(--sync-pending-color)' : 'var(--sync-ok-color)',
      }}
    >
      <span
        aria-hidden="true"
        style={{
          width: '6px',
          height: '6px',
          borderRadius: '50%',
          backgroundColor: 'currentColor',
          flexShrink: 0,
        }}
      />
      {status === 'offline'
        ? t('offline.banner')
        : pendingCount > 0
          ? t('journal.sync_pending_other', { count: pendingCount })
          : null}
    </div>
  );
}
