/**
 * GuidesScreen — /guides
 * Préparer le Chemin : liste des guides groupés par catégorie.
 *
 * Lecture publique — pas d'authentification requise.
 * Les guides couvrent : forme physique, santé/pieds/pharmacie, crédencial,
 * budget, météo/saison, faune dangereuse — groupés en "Le Corps" et "Pratique".
 */

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useGuides } from '../../../shared/hooks/useGuides.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { GuideListItemModel } from '../../../models/pilgrimage.ts';

// ─── Icône flèche ─────────────────────────────────────────────────────────────

const ChevronRightIcon = () => (
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
    style={{ flexShrink: 0, color: 'var(--color-text-tertiary)' }}
  >
    <path d="M9 18l6-6-6-6" />
  </svg>
);

// ─── Carte d'un guide ─────────────────────────────────────────────────────────

function GuideCard({ guide, onClick }: { guide: GuideListItemModel; onClick: () => void }) {
  return (
    <button
      type="button"
      onClick={onClick}
      aria-label={guide.title}
      style={{
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-3)',
        width: '100%',
        textAlign: 'left',
        background: 'none',
        border: 'none',
        padding: 'var(--space-3) var(--space-4)',
        borderBottom: '1px solid var(--color-border-subtle)',
        cursor: 'pointer',
        minHeight: '56px',
        WebkitTapHighlightColor: 'transparent',
      }}
    >
      {/* Icône guide */}
      <span
        aria-hidden="true"
        style={{
          fontSize: '24px',
          width: '36px',
          textAlign: 'center',
          flexShrink: 0,
        }}
      >
        {guide.icon}
      </span>

      {/* Titre */}
      <span style={{
        flex: 1,
        fontSize: 'var(--font-size-sm)',
        fontWeight: 'var(--font-weight-medium)',
        color: 'var(--color-text-primary)',
        fontFamily: 'var(--font-family-interface)',
      }}>
        {guide.title}
      </span>

      <ChevronRightIcon />
    </button>
  );
}

// ─── Section par catégorie ────────────────────────────────────────────────────

function GuideCategory({
  category,
  guides,
  onSelect,
}: {
  category: string;
  guides: GuideListItemModel[];
  onSelect: (slug: string) => void;
}) {
  return (
    <section aria-labelledby={`category-${category}`}>
      <h2
        id={`category-${category}`}
        style={{
          fontSize: 'var(--font-size-xs)',
          fontWeight: 'var(--font-weight-semibold)',
          letterSpacing: 'var(--letter-spacing-wide)',
          textTransform: 'uppercase',
          color: 'var(--color-text-tertiary)',
          fontFamily: 'var(--font-family-interface)',
          margin: '0 0 var(--space-2) 0',
          padding: '0 var(--space-4)',
        }}
      >
        {category}
      </h2>
      <div style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid var(--color-border-subtle)',
        overflow: 'hidden',
      }}>
        {guides.map((guide, idx) => (
          <div
            key={guide.slug}
            style={idx === guides.length - 1 ? { borderBottom: 'none' } : undefined}
          >
            <GuideCard guide={guide} onClick={() => onSelect(guide.slug)} />
          </div>
        ))}
      </div>
    </section>
  );
}

// ─── Écran principal ──────────────────────────────────────────────────────────

export function GuidesScreen() {
  const { t } = useTranslation('pilgrimage');
  const navigate = useNavigate();
  const { data: guides, isLoading, isError, refetch } = useGuides();

  // Grouper par catégorie (ordre d'insertion préservé)
  const grouped = (guides ?? []).reduce<Record<string, GuideListItemModel[]>>((acc, guide) => {
    const cat = guide.category;
    if (!acc[cat]) acc[cat] = [];
    acc[cat].push(guide);
    return acc;
  }, {});

  const categories = Object.keys(grouped);

  return (
    <div
      data-testid="guides-screen"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
      }}
    >
      {/* Header sticky */}
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
            {t('guides.title')}
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
        gap: 'var(--space-6)',
      }}>
        {/* Chargement */}
        {isLoading && <SkeletonCard count={6} />}

        {/* Erreur réseau */}
        {isError && !isLoading && (
          <div
            role="alert"
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: 'var(--space-4)',
              padding: 'var(--space-8)',
              textAlign: 'center',
            }}
          >
            <p style={{ color: 'var(--color-text-secondary)', fontSize: 'var(--font-size-sm)' }}>
              {t('guides.error')}
            </p>
            <button
              type="button"
              onClick={() => void refetch()}
              style={{
                minHeight: '44px',
                padding: '0 var(--space-6)',
                backgroundColor: 'var(--color-interactive-primary)',
                color: 'var(--color-text-inverse)',
                border: 'none',
                borderRadius: 'var(--radius-lg)',
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-medium)',
                cursor: 'pointer',
              }}
            >
              {t('error.retry')}
            </button>
          </div>
        )}

        {/* État vide */}
        {!isLoading && !isError && categories.length === 0 && (
          <EmptyState message={t('guides.empty')} />
        )}

        {/* Sections par catégorie */}
        {!isLoading && !isError && categories.map(cat => (
          <GuideCategory
            key={cat}
            category={cat}
            guides={grouped[cat] ?? []}
            onSelect={slug => navigate(`/guides/${slug}`)}
          />
        ))}
      </div>
    </div>
  );
}
