import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useStages } from '../../../shared/hooks/useStages.ts';
import { StageCard } from './StageCard.tsx';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { StageModel } from '../../../models/pilgrimage.ts';

type Country = 'BE' | 'FR' | 'ES';

const COUNTRIES: Country[] = ['BE', 'FR', 'ES'];

/**
 * Groupe les étapes par route (routeId) en préservant l'ordre de tri API.
 * Retourne un tableau de { routeId, routeName, stages[] } dans l'ordre d'apparition.
 */
function groupStagesByRoute(stages: StageModel[]): Array<{
  routeId: string;
  routeName: string | null;
  stages: StageModel[];
}> {
  const groups: Array<{ routeId: string; routeName: string | null; stages: StageModel[] }> = [];
  const seen = new Map<string, number>();

  for (const stage of stages) {
    const existing = seen.get(stage.routeId);
    if (existing !== undefined) {
      groups[existing]!.stages.push(stage);
    } else {
      const idx = groups.length;
      seen.set(stage.routeId, idx);
      groups.push({ routeId: stage.routeId, routeName: stage.routeName, stages: [stage] });
    }
  }

  return groups;
}

export function StageListScreen() {
  const { t } = useTranslation('pilgrimage');
  const navigate = useNavigate();
  const [activeCountry, setActiveCountry] = useState<Country>('BE');

  const { data: stages, isLoading, isError, error } = useStages(activeCountry);

  const isBelgique = activeCountry === 'BE';

  // BUG-P1-001 : groupement par route pour éviter l'entremêlement des étapes
  const routeGroups = stages ? groupStagesByRoute(stages) : [];
  const hasMultipleRoutes = routeGroups.length > 1;

  return (
    <div style={{
      display: 'flex',
      flexDirection: 'column',
      height: '100%',
      backgroundColor: 'var(--color-bg-base)',
    }}>
      {/* Header sticky */}
      <header style={{
        position: 'sticky',
        top: 0,
        zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        height: '56px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        flexShrink: 0,
      }}>
        <h1 style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          fontFamily: 'var(--font-family-interface)',
          color: 'var(--color-text-primary)',
          margin: 0,
          letterSpacing: 'normal',
        }}>
          {t('stages.list_title')}
        </h1>
        <button
          type="button"
          onClick={() => navigate('/carte')}
          aria-label={t('stages.fab_label')}
          style={{
            background: 'none',
            border: 'none',
            cursor: 'pointer',
            color: 'var(--color-text-accent)',
            display: 'flex',
            alignItems: 'center',
            gap: '4px',
            fontSize: 'var(--font-size-sm)',
            padding: '8px',
            borderRadius: 'var(--radius-md)',
            minWidth: '44px',
            minHeight: '44px',
            justifyContent: 'center',
          }}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
            <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
          </svg>
        </button>
      </header>

      {/* Tabs pays */}
      <div
        role="tablist"
        aria-label="Pays"
        style={{
          display: 'flex',
          backgroundColor: 'var(--color-bg-elevated)',
          borderBottom: '1px solid var(--color-border-subtle)',
          flexShrink: 0,
        }}
      >
        {COUNTRIES.map(country => {
          const labelKey = `stages.tab_${country.toLowerCase()}` as const;
          const isActive = activeCountry === country;
          return (
            <button
              key={country}
              role="tab"
              aria-selected={isActive}
              aria-controls={`panel-${country}`}
              id={`tab-${country}`}
              type="button"
              onClick={() => setActiveCountry(country)}
              style={{
                flex: 1,
                minHeight: '44px',
                background: 'none',
                border: 'none',
                borderBottom: isActive ? '2px solid var(--color-gold-500)' : '2px solid transparent',
                color: isActive ? 'var(--color-text-accent)' : 'var(--color-text-tertiary)',
                fontWeight: isActive ? 'var(--font-weight-medium)' : 'var(--font-weight-regular)',
                fontSize: 'var(--font-size-sm)',
                cursor: 'pointer',
                fontFamily: 'var(--font-family-interface)',
                transition: 'color var(--duration-fast) var(--easing-default)',
              }}
            >
              {t(labelKey)}
            </button>
          );
        })}
      </div>

      {/* Contenu scrollable */}
      <div
        id={`panel-${activeCountry}`}
        role="tabpanel"
        aria-labelledby={`tab-${activeCountry}`}
        style={{
          flex: 1,
          overflowY: 'auto',
          padding: 'var(--space-4)',
          paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-4))',
          display: 'flex',
          flexDirection: 'column',
          gap: 'var(--space-3)',
          WebkitOverflowScrolling: 'touch',
        }}
      >
        {/* Erreur offline */}
        {isError && (
          <div role="alert" style={{
            backgroundColor: 'rgba(192,64,64,0.1)',
            border: '1px solid var(--color-error-500)',
            borderRadius: 'var(--radius-md)',
            padding: 'var(--space-4)',
            fontSize: 'var(--font-size-sm)',
            color: 'var(--color-text-secondary)',
          }}>
            {error?.message?.includes('Failed to fetch')
              ? t('error.offline_fetch')
              : t('error.sync_failed')}
          </div>
        )}

        {/* France / Espagne : message "bientôt" */}
        {!isBelgique && !isLoading && (
          <EmptyState message={t(`stages.empty_${activeCountry.toLowerCase()}` as 'stages.empty_fr' | 'stages.empty_es')} />
        )}

        {/* Loading skeleton */}
        {isLoading && isBelgique && <SkeletonCard count={6} />}

        {/* BUG-P1-001 — Liste étapes groupées par voie */}
        {isBelgique && stages && routeGroups.map(group => (
          <div key={group.routeId} role="list" aria-label={group.routeName ?? t('stages.list_title')}>
            {/* En-tête de voie — affiché uniquement si plusieurs routes coexistent */}
            {hasMultipleRoutes && group.routeName && (
              <div
                data-testid={`route-group-header-${group.routeId}`}
                style={{
                  fontSize: 'var(--font-size-xs)',
                  fontWeight: 'var(--font-weight-semibold)',
                  letterSpacing: 'var(--letter-spacing-wide)',
                  textTransform: 'uppercase',
                  color: 'var(--color-text-tertiary)',
                  fontFamily: 'var(--font-family-interface)',
                  padding: 'var(--space-2) 0 var(--space-1) 0',
                  borderBottom: '1px solid var(--color-border-subtle)',
                  marginBottom: 'var(--space-2)',
                }}
              >
                {group.routeName}
              </div>
            )}
            {group.stages.map(stage => (
              <div key={stage.id} style={{ marginBottom: 'var(--space-3)' }}>
                <StageCard stage={stage} />
              </div>
            ))}
          </div>
        ))}

        {isBelgique && stages?.length === 0 && !isLoading && (
          <EmptyState message={t('error.offline_fetch')} />
        )}
      </div>
    </div>
  );
}
