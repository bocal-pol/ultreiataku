import { useTranslation } from 'react-i18next';

export function JournalPlaceholder() {
  const { t } = useTranslation('pilgrimage');
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100%',
      color: 'var(--color-text-tertiary)',
      fontSize: 'var(--font-size-md)',
      padding: 'var(--space-8)',
      textAlign: 'center',
    }}>
      {t('placeholder.journal_coming_soon')}
    </div>
  );
}
