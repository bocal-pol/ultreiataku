/**
 * PackScenarioDetailScreen — /sac/:id
 * Détail d'un scénario de sac : jauge RG-01, sections par catégorie, items.
 * ULTREIA-44 — Vague 1d
 */

import { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { usePackScenario } from '../../../shared/hooks/usePack.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { PackItemModel, PackCategory, WeightIndicator } from '../../../models/pack.ts';

// ─── Constantes catégories ────────────────────────────────────────────────────

const CATEGORIES: PackCategory[] = [
  'portage', 'sleeping', 'cooking', 'water',
  'clothing', 'hygiene', 'health', 'navigation', 'misc',
];

// ─── Jauge de poids RG-01 ────────────────────────────────────────────────────

interface WeightGaugeProps {
  baseWeightKg: number;
  totalWeightKg: number;
  targetBaseWeightKg: number | null;
  indicator: WeightIndicator;
}

function WeightGauge({ baseWeightKg, totalWeightKg, targetBaseWeightKg, indicator }: WeightGaugeProps) {
  const { t } = useTranslation('pilgrimage');

  const colorMap: Record<WeightIndicator, string> = {
    ok: 'var(--color-camp-green, #5a9e5a)',
    warn: 'var(--color-detour-amber, #d4840a)',
    over: 'var(--color-error, #e8503a)',
  };

  const gaugeColor = colorMap[indicator];

  const fillPercent = targetBaseWeightKg
    ? Math.min((baseWeightKg / (targetBaseWeightKg + 2)) * 100, 100)
    : 50;

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
    <div
      data-testid="pack-weight-gauge"
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
      {/* Totaux */}
      <div style={{ display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: 'var(--space-2)' }}>
        <div>
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
            {t('pack.base_label')}
          </div>
          <div style={{ fontSize: 'var(--font-size-lg)', fontWeight: 'var(--font-weight-semibold)', color: gaugeColor }}>
            {baseWeightKg.toFixed(2)} kg
          </div>
        </div>
        <div style={{ textAlign: 'right' }}>
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
            {t('pack.total_label', { x: totalWeightKg.toFixed(2) })}
          </div>
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
            {t('pack.water_label')} · {t('pack.food_label')}
          </div>
        </div>
      </div>

      {/* Barre de progression */}
      {targetBaseWeightKg !== null && (
        <div
          role="meter"
          aria-valuenow={baseWeightKg}
          aria-valuemin={0}
          aria-valuemax={targetBaseWeightKg + 2}
          aria-label={`Poids base : ${baseWeightKg.toFixed(2)} kg / objectif ${targetBaseWeightKg} kg`}
          style={{
            height: '8px',
            backgroundColor: 'var(--color-border-subtle)',
            borderRadius: 'var(--radius-full)',
            overflow: 'hidden',
          }}
        >
          <div style={{
            height: '100%',
            width: `${fillPercent}%`,
            backgroundColor: gaugeColor,
            borderRadius: 'var(--radius-full)',
            transition: 'width 0.3s ease, background-color 0.3s ease',
          }} />
        </div>
      )}

      {/* Statut */}
      <div style={{ fontSize: 'var(--font-size-sm)', color: gaugeColor, fontWeight: 'var(--font-weight-medium)' }}>
        {t(labelKey, { delta: delta ?? '?' })}
        {targetBaseWeightKg !== null && (
          <span style={{ color: 'var(--color-text-tertiary)', fontWeight: 'var(--font-weight-normal)', marginLeft: 'var(--space-2)' }}>
            (objectif : {targetBaseWeightKg} kg)
          </span>
        )}
      </div>
    </div>
  );
}

// ─── Item row ─────────────────────────────────────────────────────────────────

interface PackItemRowProps {
  item: PackItemModel;
}

function PackItemRow({ item }: PackItemRowProps) {
  return (
    <div
      data-testid="pack-item-row"
      style={{
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-3)',
        padding: 'var(--space-3) 0',
        borderBottom: '1px solid var(--color-border-subtle)',
        minHeight: '44px',
      }}
    >
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-text-primary)',
          fontWeight: 'var(--font-weight-medium)',
          whiteSpace: 'nowrap',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
        }}>
          {item.name}
        </div>
        {(item.brand || item.model) && (
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
            {[item.brand, item.model].filter(Boolean).join(' ')}
          </div>
        )}
      </div>

      {/* Badges */}
      <div style={{ display: 'flex', gap: 'var(--space-1)', flexShrink: 0 }}>
        {item.isShared && (
          <span
            aria-label="Partagé"
            style={{
              fontSize: 'var(--font-size-xs)',
              color: 'var(--color-gold-500)',
              backgroundColor: 'var(--color-gold-500)18',
              borderRadius: 'var(--radius-full)',
              padding: '1px 6px',
            }}
          >
            ↔
          </span>
        )}
        {item.isConsumable && (
          <span
            aria-label="Consommable"
            style={{
              fontSize: 'var(--font-size-xs)',
              color: 'var(--color-text-tertiary)',
              backgroundColor: 'var(--color-bg-base)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-full)',
              padding: '1px 6px',
            }}
          >
            ◎
          </span>
        )}
      </div>

      {/* Poids */}
      <div style={{
        fontSize: 'var(--font-size-sm)',
        color: 'var(--color-text-secondary)',
        fontVariantNumeric: 'tabular-nums',
        flexShrink: 0,
        minWidth: '52px',
        textAlign: 'right',
      }}>
        {item.weightG < 1000
          ? `${item.weightG} g`
          : `${(item.weightG / 1000).toFixed(2)} kg`}
      </div>
    </div>
  );
}

// ─── Section catégorie ────────────────────────────────────────────────────────

interface CategorySectionProps {
  categoryKey: PackCategory;
  items: PackItemModel[];
}

function CategorySection({ categoryKey, items }: CategorySectionProps) {
  const { t } = useTranslation('pilgrimage');
  const [expanded, setExpanded] = useState(true);

  const totalG = items.reduce((sum, i) => sum + i.weightG, 0);
  const categoryLabel = t(`pack.categories.${categoryKey}` as Parameters<typeof t>[0]);
  const sectionId = `cat-${categoryKey}`;

  return (
    <section
      data-testid="pack-category-section"
      aria-labelledby={sectionId}
      style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid var(--color-border-subtle)',
        overflow: 'hidden',
      }}
    >
      <button
        type="button"
        id={sectionId}
        aria-expanded={expanded}
        aria-controls={`${sectionId}-content`}
        onClick={() => setExpanded(e => !e)}
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          width: '100%',
          padding: 'var(--space-3) var(--space-4)',
          background: 'none',
          border: 'none',
          cursor: 'pointer',
          minHeight: '44px',
          gap: 'var(--space-3)',
        }}
      >
        <span style={{
          fontSize: 'var(--font-size-sm)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-secondary)',
          textTransform: 'uppercase',
          letterSpacing: 'var(--letter-spacing-wide)',
          textAlign: 'left',
        }}>
          {categoryLabel}
        </span>
        <div style={{ display: 'flex', gap: 'var(--space-2)', alignItems: 'center', flexShrink: 0 }}>
          <span style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
            {totalG < 1000 ? `${totalG} g` : `${(totalG / 1000).toFixed(2)} kg`}
          </span>
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            style={{
              color: 'var(--color-text-tertiary)',
              transform: expanded ? 'rotate(180deg)' : 'rotate(0deg)',
              transition: 'transform 0.2s ease',
            }}
          >
            <polyline points="6 9 12 15 18 9" />
          </svg>
        </div>
      </button>

      {expanded && (
        <div
          id={`${sectionId}-content`}
          style={{ padding: '0 var(--space-4) var(--space-2)' }}
        >
          {items.map(item => (
            <PackItemRow key={item.id} item={item} />
          ))}
        </div>
      )}
    </section>
  );
}

// ─── Écran principal ─────────────────────────────────────────────────────────

export function PackScenarioDetailScreen() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');

  const { data: scenario, isLoading, isError } = usePackScenario(id ?? '');

  if (isLoading) {
    return <div style={{ padding: 'var(--space-4)' }}><SkeletonCard count={4} /></div>;
  }

  if (isError || !scenario) {
    return <EmptyState message={t('error.stage_not_found')} />;
  }

  // Grouper les items par catégorie
  const itemsByCategory = CATEGORIES.reduce<Record<PackCategory, PackItemModel[]>>(
    (acc, cat) => {
      acc[cat] = scenario.items.filter(i => i.category === cat);
      return acc;
    },
    {} as Record<PackCategory, PackItemModel[]>,
  );

  return (
    <div
      data-testid="pack-scenario-detail"
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
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', gap: 'var(--space-3)' }}>
          <button
            type="button"
            onClick={() => navigate('/sac')}
            aria-label={t('nav.pack')}
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              color: 'var(--color-text-accent)', padding: '8px',
              borderRadius: 'var(--radius-md)',
              display: 'flex', alignItems: 'center',
              minWidth: '44px', minHeight: '44px', justifyContent: 'center',
            }}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6" />
            </svg>
          </button>
          <h1 style={{
            flex: 1,
            margin: 0,
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            color: 'var(--color-text-primary)',
            fontFamily: 'var(--font-family-interface)',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
            textOverflow: 'ellipsis',
          }}>
            {scenario.name}
          </h1>
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
        gap: 'var(--space-4)',
      }}>
        {/* Jauge de poids */}
        <WeightGauge
          baseWeightKg={scenario.baseWeightKg}
          totalWeightKg={scenario.totalWeightKg}
          targetBaseWeightKg={scenario.targetBaseWeightKg}
          indicator={scenario.weightIndicator}
        />

        {/* Sections par catégorie (seulement celles avec items) */}
        {CATEGORIES.filter(cat => itemsByCategory[cat].length > 0).map(cat => (
          <CategorySection
            key={cat}
            categoryKey={cat}
            items={itemsByCategory[cat]}
          />
        ))}

        {scenario.items.length === 0 && (
          <EmptyState message={t('pack.add_item')} />
        )}
      </div>
    </div>
  );
}
