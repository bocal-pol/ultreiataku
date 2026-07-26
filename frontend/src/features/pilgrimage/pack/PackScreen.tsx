/**
 * PackScreen — /sac
 * Liste des scénarios de sac du pèlerin connecté.
 * ULTREIA-44 — Vague 1d
 */

import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../../context/AuthContext.tsx';
import { usePackScenarios } from '../../../shared/hooks/usePack.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { PackScenarioModel } from '../../../models/pack.ts';

// ─── Sous-composants ─────────────────────────────────────────────────────────

interface WeightGaugeBadgeProps {
  indicator: PackScenarioModel['weightIndicator'];
  baseWeightKg: number;
  targetBaseWeightKg: number | null;
}

function WeightGaugeBadge({ indicator, baseWeightKg, targetBaseWeightKg }: WeightGaugeBadgeProps) {
  const { t } = useTranslation('pilgrimage');

  const colorMap: Record<PackScenarioModel['weightIndicator'], string> = {
    ok: 'var(--color-camp-green, #5a9e5a)',
    warn: 'var(--color-detour-amber, #d4840a)',
    over: 'var(--color-error, #e8503a)',
  };

  const delta = targetBaseWeightKg !== null
    ? Math.abs(baseWeightKg - targetBaseWeightKg).toFixed(1)
    : null;

  const labelKey =
    indicator === 'ok'
      ? ('pack.weight_status.ok' as const)
      : indicator === 'warn'
        ? ('pack.weight_status.warn' as const)
        : ('pack.weight_status.over' as const);

  return (
    <span
      style={{
        display: 'inline-block',
        fontSize: 'var(--font-size-xs)',
        fontWeight: 'var(--font-weight-semibold)',
        color: colorMap[indicator],
        backgroundColor: `${colorMap[indicator]}18`,
        borderRadius: 'var(--radius-full)',
        padding: '2px var(--space-2)',
      }}
    >
      {t(labelKey, { delta: delta ?? '?' })}
    </span>
  );
}

interface ScenarioCardProps {
  scenario: PackScenarioModel;
  onClick: () => void;
}

function ScenarioCard({ scenario, onClick }: ScenarioCardProps) {
  const { t } = useTranslation('pilgrimage');

  return (
    <button
      type="button"
      data-testid="pack-scenario-item"
      onClick={onClick}
      style={{
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-2)',
        width: '100%',
        textAlign: 'left',
        backgroundColor: 'var(--color-bg-elevated)',
        border: '1px solid var(--color-border-subtle)',
        borderRadius: 'var(--radius-lg)',
        padding: 'var(--space-4)',
        cursor: 'pointer',
        minHeight: '44px',
      }}
    >
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        gap: 'var(--space-2)',
      }}>
        <span style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-primary)',
        }}>
          {scenario.name}
        </span>
        <WeightGaugeBadge
          indicator={scenario.weightIndicator}
          baseWeightKg={scenario.baseWeightKg}
          targetBaseWeightKg={scenario.targetBaseWeightKg}
        />
      </div>

      <div style={{
        display: 'flex',
        gap: 'var(--space-4)',
        fontSize: 'var(--font-size-sm)',
        color: 'var(--color-text-secondary)',
      }}>
        <span>{t('pack.base_label')} : {scenario.baseWeightKg.toFixed(2)} kg</span>
        <span>{t('pack.total_label', { x: scenario.totalWeightKg.toFixed(2) })}</span>
      </div>

      {scenario.targetBaseWeightKg !== null && (
        <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
          Objectif : {scenario.targetBaseWeightKg} kg base
        </div>
      )}
    </button>
  );
}

// ─── Écran principal ─────────────────────────────────────────────────────────

export function PackScreen() {
  const { t } = useTranslation('pilgrimage');
  const navigate = useNavigate();
  const { currentUser } = useAuth();

  const pilgrimId = currentUser?.pilgrim.id ?? '';
  const { data: scenarios, isLoading, isError } = usePackScenarios(pilgrimId);

  return (
    <div
      data-testid="pack-screen"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
      }}
    >
      {/* Header */}
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
            {t('nav.pack')}
          </h1>
        </div>
      </header>

      {/* Contenu */}
      <div style={{
        flex: 1,
        overflowY: 'auto',
        WebkitOverflowScrolling: 'touch',
        padding: 'var(--space-4)',
        paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-8))',
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-3)',
      }}>
        {!currentUser && (
          <EmptyState message={t('auth.login_required')} />
        )}

        {currentUser && isLoading && (
          <SkeletonCard count={3} />
        )}

        {currentUser && isError && (
          <EmptyState message={t('error.sync_failed')} />
        )}

        {currentUser && !isLoading && !isError && (
          <div
            data-testid="pack-scenario-list"
            style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}
          >
            {(scenarios ?? []).length === 0 ? (
              <EmptyState message={t('pack.manage_scenarios')} />
            ) : (
              (scenarios ?? []).map(scenario => (
                <ScenarioCard
                  key={scenario.id}
                  scenario={scenario}
                  onClick={() => navigate(`/sac/${scenario.id}`)}
                />
              ))
            )}
          </div>
        )}
      </div>
    </div>
  );
}
