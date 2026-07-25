interface SkeletonCardProps {
  count?: number;
}

function pulse(delay: string) {
  return {
    backgroundColor: 'var(--color-bg-overlay)',
    borderRadius: 'var(--radius-md)',
    animation: `skeleton-pulse 1.5s ease-in-out ${delay} infinite`,
  } as React.CSSProperties;
}

export function SkeletonCard({ count = 5 }: SkeletonCardProps) {
  return (
    <>
      <style>{`
        @keyframes skeleton-pulse {
          0%, 100% { opacity: 1; }
          50% { opacity: 0.4; }
        }
      `}</style>
      {Array.from({ length: count }).map((_, i) => (
        <div
          key={i}
          aria-hidden="true"
          style={{
            backgroundColor: 'var(--stage-card-bg)',
            borderRadius: 'var(--stage-card-radius)',
            border: '1px solid var(--stage-card-border)',
            padding: 'var(--stage-card-padding)',
            display: 'flex',
            gap: '12px',
            alignItems: 'center',
            minHeight: '72px',
          }}
        >
          <div style={{ ...pulse(`${i * 0.1}s`), width: '36px', height: '36px', borderRadius: '50%', flexShrink: 0 }} />
          <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: '8px' }}>
            <div style={{ ...pulse(`${i * 0.1}s`), height: '14px', width: '60%' }} />
            <div style={{ ...pulse(`${i * 0.1 + 0.05}s`), height: '12px', width: '40%' }} />
          </div>
        </div>
      ))}
    </>
  );
}
