/**
 * GuideDetailScreen — /guides/:slug
 * Affiche le contenu markdown d'un guide du Chemin.
 *
 * Lecture publique — pas d'authentification requise.
 * Le rendu markdown est assuré par MarkdownRenderer (renderer léger interne).
 */

import { useParams, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useGuideDetail } from '../../../shared/hooks/useGuides.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { MarkdownRenderer } from '../../../shared/ui/MarkdownRenderer.tsx';

// ─── Icône retour ─────────────────────────────────────────────────────────────

const ArrowLeftIcon = () => (
  <svg
    width="20"
    height="20"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    aria-hidden="true"
  >
    <path d="M19 12H5M12 5l-7 7 7 7" />
  </svg>
);

// ─── Écran principal ──────────────────────────────────────────────────────────

export function GuideDetailScreen() {
  const { slug = '' } = useParams<{ slug: string }>();
  const { t } = useTranslation('pilgrimage');
  const { data: guide, isLoading, isError } = useGuideDetail(slug);

  return (
    <div
      data-testid="guide-detail-screen"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
      }}
    >
      {/* Header sticky avec retour */}
      <header style={{
        position: 'sticky',
        top: 0,
        zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{
          display: 'flex',
          alignItems: 'center',
          gap: 'var(--space-3)',
          height: '56px',
        }}>
          <Link
            to="/guides"
            aria-label={t('guides.back')}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 'var(--space-1)',
              color: 'var(--color-text-accent)',
              textDecoration: 'none',
              fontSize: 'var(--font-size-sm)',
              minHeight: '44px',
              minWidth: '44px',
              justifyContent: 'flex-start',
            }}
          >
            <ArrowLeftIcon />
            <span>{t('guides.back')}</span>
          </Link>
        </div>
      </header>

      {/* Contenu scrollable */}
      <div style={{
        flex: 1,
        overflowY: 'auto',
        WebkitOverflowScrolling: 'touch',
        padding: 'var(--space-4)',
        paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-8))',
      }}>
        {/* Chargement */}
        {isLoading && <SkeletonCard count={4} />}

        {/* Erreur / guide introuvable */}
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
              {t('error.stage_not_found')}
            </p>
            <Link
              to="/guides"
              style={{
                display: 'inline-flex',
                alignItems: 'center',
                minHeight: '44px',
                padding: '0 var(--space-6)',
                backgroundColor: 'var(--color-interactive-primary)',
                color: 'var(--color-text-inverse)',
                borderRadius: 'var(--radius-lg)',
                textDecoration: 'none',
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-medium)',
              }}
            >
              {t('guides.back')}
            </Link>
          </div>
        )}

        {/* Contenu du guide */}
        {!isLoading && !isError && guide && (
          <article aria-labelledby="guide-title">
            {/* En-tête du guide */}
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: 'var(--space-3)',
              marginBottom: 'var(--space-2)',
            }}>
              <span
                aria-hidden="true"
                style={{ fontSize: '32px', flexShrink: 0 }}
              >
                {guide.icon}
              </span>
              <div>
                <p style={{
                  margin: 0,
                  fontSize: 'var(--font-size-xs)',
                  color: 'var(--color-text-tertiary)',
                  textTransform: 'uppercase',
                  letterSpacing: 'var(--letter-spacing-wide)',
                  fontWeight: 'var(--font-weight-semibold)',
                }}>
                  {guide.category}
                </p>
                <h1
                  id="guide-title"
                  style={{
                    margin: 0,
                    fontSize: 'var(--font-size-xl)',
                    fontWeight: 'var(--font-weight-bold)',
                    color: 'var(--color-text-primary)',
                    fontFamily: 'var(--font-family-display)',
                  }}
                >
                  {guide.title}
                </h1>
              </div>
            </div>

            <hr style={{
              border: 'none',
              borderTop: '1px solid var(--color-border-subtle)',
              margin: 'var(--space-4) 0',
            }} />

            {/* Corps markdown */}
            <MarkdownRenderer content={guide.content} />
          </article>
        )}
      </div>
    </div>
  );
}
