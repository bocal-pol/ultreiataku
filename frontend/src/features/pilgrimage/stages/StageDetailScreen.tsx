import { useParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Suspense, lazy, useState } from 'react';
import { useStageDetail } from '../../../shared/hooks/useStages.ts';
import { useGpxSimplified } from '../../../shared/hooks/useGpx.ts';
import { DetourBadge } from '../../../shared/ui/DetourBadge.tsx';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { WaypointModel, MealModel, AccommodationModel } from '../../../models/pilgrimage.ts';

const MiniMap = lazy(() => import('../map/MiniMap.tsx'));

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Retourne le nombre de mois entre une date ISO et maintenant (arrondi). */
function monthsAgo(isoDate: string): number {
  const past = new Date(isoDate).getTime();
  const now = Date.now();
  return Math.round((now - past) / (1000 * 60 * 60 * 24 * 30));
}

// ── Sous-composants ───────────────────────────────────────────────────────────

function SectionHeader({ title }: { title: string }) {
  return (
    <h2 style={{
      fontSize: 'var(--font-size-xs)',
      fontWeight: 'var(--font-weight-semibold)',
      letterSpacing: 'var(--letter-spacing-wide)',
      textTransform: 'uppercase',
      color: 'var(--color-text-tertiary)',
      fontFamily: 'var(--font-family-interface)',
      margin: '0 0 var(--space-3) 0',
    }}>
      {title}
    </h2>
  );
}

function PoiItem({ wp }: { wp: WaypointModel }) {
  const { t } = useTranslation('pilgrimage');
  const isPoi = wp.type === 'poi';

  return (
    <div style={{
      padding: 'var(--space-3) 0',
      borderBottom: '1px solid var(--color-border-subtle)',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
        <span style={{ fontSize: 'var(--font-size-md)', color: 'var(--color-text-primary)', fontWeight: 'var(--font-weight-medium)' }}>
          {isPoi && <span aria-hidden="true">★ </span>}
          {wp.name}
        </span>
        {wp.detourType && <DetourBadge type={wp.detourType} />}
      </div>

      {wp.detourDistanceKm && wp.detourDurationMin !== null && wp.visitDurationMin !== null && (
        <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', marginTop: '4px' }}>
          {t('poi.detour_format', {
            km: wp.detourDistanceKm,
            walk: wp.detourDurationMin,
            visit: wp.visitDurationMin,
          })}
        </div>
      )}
      {wp.entryCostEur !== null && wp.entryCostEur > 0 && (
        <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
          {t('poi.cost_format', { cost: wp.entryCostEur })}
        </div>
      )}
      {wp.bookingRequired && (
        <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-detour-amber)', marginTop: '2px' }}>
          {t('poi.booking_required')}
        </div>
      )}
      {wp.bookingContact && (
        <a
          href={wp.bookingContact.includes('@') ? `mailto:${wp.bookingContact}` : `tel:${wp.bookingContact}`}
          style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-accent)', display: 'block', marginTop: '2px', minHeight: '44px', lineHeight: '44px' }}
        >
          {wp.bookingContact}
        </a>
      )}
      {wp.description && (
        <p style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', marginTop: '4px', lineHeight: 'var(--line-height-relaxed)', margin: '4px 0 0' }}>
          {wp.description}
        </p>
      )}
    </div>
  );
}

// ── Amenity badge ─────────────────────────────────────────────────────────────

function Amenity({ label }: { label: string }) {
  return (
    <span
      aria-label={label}
      style={{
        fontSize: 'var(--font-size-xs)',
        color: 'var(--color-camp-green)',
        backgroundColor: 'rgba(90,158,90,0.12)',
        borderRadius: 'var(--radius-full)',
        padding: '2px 8px',
      }}
    >
      ✓ {label}
    </span>
  );
}

// ── Badge compact inline ──────────────────────────────────────────────────────

function InlineBadge({ label, color }: { label: string; color?: string }) {
  return (
    <span style={{
      fontSize: 'var(--font-size-xs)',
      color: color ?? 'var(--color-text-secondary)',
      backgroundColor: 'rgba(200,150,60,0.1)',
      borderRadius: 'var(--radius-full)',
      padding: '2px 8px',
      border: '1px solid rgba(200,150,60,0.25)',
    }}>
      {label}
    </span>
  );
}

// ── ULTREIA-24 — Hébergement ──────────────────────────────────────────────────

function AccommodationContact({ accom }: { accom: AccommodationModel }) {
  const lines: Array<{ href: string; label: string }> = [];
  if (accom.phone) lines.push({ href: `tel:${accom.phone}`, label: accom.phone });
  if (accom.email) lines.push({ href: `mailto:${accom.email}`, label: accom.email });

  if (lines.length === 0 && !accom.website) return null;

  return (
    <div style={{ marginTop: 'var(--space-2)', display: 'flex', flexDirection: 'column', gap: '2px' }}>
      {lines.map(({ href, label }) => (
        <a
          key={href}
          href={href}
          style={{
            fontSize: 'var(--font-size-sm)',
            color: 'var(--color-text-accent)',
            minHeight: '44px',
            lineHeight: '44px',
            display: 'block',
          }}
        >
          {label}
        </a>
      ))}
      {accom.website && (
        <a
          href={accom.website}
          target="_blank"
          rel="noopener noreferrer"
          style={{
            fontSize: 'var(--font-size-sm)',
            color: 'var(--color-text-accent)',
            minHeight: '44px',
            lineHeight: '44px',
            display: 'block',
          }}
        >
          {accom.website}
        </a>
      )}
    </div>
  );
}

function AccommodationVerifiedBadge({ verifiedAt }: { verifiedAt: string | null }) {
  const { t } = useTranslation('pilgrimage');
  if (!verifiedAt) return null;
  const months = monthsAgo(verifiedAt);
  // Ne montrer le badge "ancien" que si > 6 mois
  if (months <= 6) return null;

  return (
    <span
      title={t('accommodation.verified_months_ago', { count: months })}
      style={{
        fontSize: 'var(--font-size-xs)',
        color: 'var(--color-detour-amber)',
        backgroundColor: 'rgba(232,152,58,0.1)',
        borderRadius: 'var(--radius-full)',
        padding: '2px 8px',
        border: '1px solid rgba(232,152,58,0.3)',
      }}
    >
      {t('accommodation.verified_months_ago', { count: months })}
    </span>
  );
}

function AccommodationCard({ accom, isPrimary }: { accom: AccommodationModel; isPrimary: boolean }) {
  const { t } = useTranslation('pilgrimage');

  const priceLabel = (() => {
    if (accom.isDonativo) return t('accommodation.types.donativo');
    if (accom.priceMinEur === 0) return t('accommodation.price_free');
    if (accom.priceMinEur !== null && accom.priceMaxEur !== null && accom.priceMinEur !== accom.priceMaxEur) {
      return `${accom.priceMinEur}–${accom.priceMaxEur} €`;
    }
    if (accom.priceMinEur !== null) return `${accom.priceMinEur} €`;
    return null;
  })();

  return (
    <div
      data-testid={isPrimary ? 'accommodation-primary' : 'accommodation-alternative'}
      style={{
        backgroundColor: 'var(--color-bg-elevated)',
        borderRadius: 'var(--radius-lg)',
        border: isPrimary
          ? '1px solid var(--color-gold-500)'
          : '1px solid var(--color-border-subtle)',
        padding: 'var(--space-4)',
        marginBottom: 'var(--space-3)',
        opacity: accom.isObsolete ? 0.6 : 1,
      }}
    >
      {/* Ligne titre + prix */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontWeight: 'var(--font-weight-semibold)', color: 'var(--color-text-primary)', fontSize: 'var(--font-size-md)' }}>
            {accom.name}
          </div>
          <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-tertiary)', marginTop: '2px' }}>
            {t(`accommodation.types.${accom.type}`, { defaultValue: accom.type })}
          </div>
          {accom.address && (
            <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: '2px' }}>
              {accom.address}
            </div>
          )}
        </div>
        {priceLabel && (
          <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', textAlign: 'right', marginLeft: 'var(--space-3)', flexShrink: 0 }}>
            {priceLabel}
          </div>
        )}
      </div>

      {/* Équipements */}
      <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginTop: 'var(--space-3)' }}>
        {accom.hasShower && <Amenity label={t('accommodation.amenities.shower')} />}
        {accom.hasKitchen && <Amenity label={t('accommodation.amenities.kitchen')} />}
        {accom.hasWifi && <Amenity label={t('accommodation.amenities.wifi')} />}
        {accom.stampsCredencial && <Amenity label={t('accommodation.amenities.stamp')} />}
      </div>

      {/* Badges secondaires */}
      <div style={{ display: 'flex', gap: '6px', flexWrap: 'wrap', marginTop: accom.hasShower || accom.hasKitchen || accom.hasWifi || accom.stampsCredencial ? 'var(--space-2)' : 'var(--space-3)' }}>
        {accom.bookingRequired && (
          <InlineBadge
            label={accom.bookingNoticeDays
              ? t('accommodation.booking_required_days', { count: accom.bookingNoticeDays })
              : t('accommodation.booking_required')}
            color="var(--color-detour-amber)"
          />
        )}
        <AccommodationVerifiedBadge verifiedAt={accom.verifiedAt} />
      </div>

      {/* Capacité */}
      {accom.capacity !== null && (
        <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: 'var(--space-2)' }}>
          {t('accommodation.capacity', { count: accom.capacity })}
        </div>
      )}

      {/* Notes */}
      {accom.notes && (
        <p style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', marginTop: 'var(--space-2)', lineHeight: 'var(--line-height-relaxed)', margin: 'var(--space-2) 0 0' }}>
          {accom.notes}
        </p>
      )}

      {/* Contact */}
      <AccommodationContact accom={accom} />
    </div>
  );
}

function BivouacZone({ notes }: { notes: string | null }) {
  const { t } = useTranslation('pilgrimage');
  return (
    <div
      data-testid="bivouac-zone"
      style={{
        backgroundColor: 'rgba(90,158,90,0.08)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid rgba(90,158,90,0.25)',
        padding: 'var(--space-3) var(--space-4)',
        marginBottom: 'var(--space-3)',
        display: 'flex',
        gap: '8px',
        alignItems: 'flex-start',
      }}
    >
      <span aria-hidden="true" style={{ fontSize: 'var(--font-size-md)', flexShrink: 0 }}>⛺</span>
      <div>
        <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-camp-green)', fontWeight: 'var(--font-weight-medium)' }}>
          {t('stage.bivouac_legal')}
        </div>
        {notes && (
          <p style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', margin: '4px 0 0', lineHeight: 'var(--line-height-relaxed)' }}>
            {notes}
          </p>
        )}
      </div>
    </div>
  );
}

/** Accordéon pour les hébergements alternatifs */
function AltAccommodationsAccordion({ items }: { items: AccommodationModel[] }) {
  const { t } = useTranslation('pilgrimage');
  const [open, setOpen] = useState(false);

  return (
    <div style={{ marginTop: 'var(--space-2)' }}>
      <button
        type="button"
        onClick={() => setOpen(prev => !prev)}
        aria-expanded={open}
        style={{
          background: 'none',
          border: 'none',
          cursor: 'pointer',
          display: 'flex',
          alignItems: 'center',
          gap: '6px',
          fontSize: 'var(--font-size-xs)',
          color: 'var(--color-text-tertiary)',
          padding: 'var(--space-2) 0',
          minHeight: '44px',
          fontFamily: 'var(--font-family-interface)',
        }}
      >
        <span aria-hidden="true" style={{ transform: open ? 'rotate(90deg)' : 'none', display: 'inline-block', transition: 'transform 0.15s' }}>▶</span>
        {t('stage.accommodation_alt')} ({items.length})
      </button>
      {open && (
        <div>
          {items.map(a => <AccommodationCard key={a.id} accom={a} isPrimary={false} />)}
        </div>
      )}
    </div>
  );
}

// ── ULTREIA-25 — Repas ────────────────────────────────────────────────────────

const MEAL_ICONS: Record<string, string> = {
  breakfast: '🌅',
  lunch: '☀️',
  dinner: '🌙',
  snack: '🥐',
};

const MEAL_LABEL_KEYS: Record<string, 'stage.meal_morning' | 'stage.meal_lunch' | 'stage.meal_dinner' | 'stage.meal_snack'> = {
  breakfast: 'stage.meal_morning',
  lunch: 'stage.meal_lunch',
  dinner: 'stage.meal_dinner',
  snack: 'stage.meal_snack',
};

/** Carte spécialité locale — mise en avant visuelle */
function LocalSpecialtyCard({ meal }: { meal: MealModel }) {
  const { t } = useTranslation('pilgrimage');
  return (
    <div
      data-testid="meal-local-specialty"
      style={{
        backgroundColor: 'rgba(200,150,60,0.08)',
        borderRadius: 'var(--radius-lg)',
        border: '1px solid rgba(200,150,60,0.3)',
        padding: 'var(--space-3) var(--space-4)',
        marginBottom: 'var(--space-3)',
        display: 'flex',
        gap: '10px',
        alignItems: 'flex-start',
      }}
    >
      <span aria-hidden="true" style={{ fontSize: '1.2em', flexShrink: 0 }}>★</span>
      <div>
        <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-accent)', fontWeight: 'var(--font-weight-semibold)' }}>
          {meal.name}
        </div>
        <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: '2px' }}>
          {t('meal.context.local_specialty')}
        </div>
        {meal.description && (
          <p style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', margin: '4px 0 0', lineHeight: 'var(--line-height-relaxed)' }}>
            {meal.description}
          </p>
        )}
        {meal.restaurantAddress && (
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: '4px' }}>
            {meal.restaurantAddress}
          </div>
        )}
        {meal.priceEstimateEur !== null && (
          <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: '4px' }}>
            ~{meal.priceEstimateEur} €
          </div>
        )}
      </div>
    </div>
  );
}

function MealRow({ meal }: { meal: MealModel }) {
  const { t } = useTranslation('pilgrimage');
  const icon = MEAL_ICONS[meal.mealType] ?? '🍽️';
  const labelKey = MEAL_LABEL_KEYS[meal.mealType] ?? 'stage.meal_morning';
  const displayName = meal.restaurantName ?? meal.name;

  return (
    <div
      data-testid={`meal-row-${meal.mealType}`}
      style={{
        padding: 'var(--space-2) 0',
        borderBottom: '1px solid var(--color-border-subtle)',
      }}
    >
      <div style={{ display: 'flex', gap: '8px', alignItems: 'flex-start' }}>
        <span aria-hidden="true" style={{ marginTop: '2px' }}>{icon}</span>
        <div style={{ flex: 1 }}>
          <div style={{ display: 'flex', gap: '8px', alignItems: 'baseline', flexWrap: 'wrap' }}>
            <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-tertiary)', fontWeight: 'var(--font-weight-medium)', minWidth: '64px' }}>
              {t(labelKey)}
            </span>
            <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
              {displayName}
            </span>
          </div>
          {meal.restaurantAddress && (
            <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: '2px' }}>
              {meal.restaurantAddress}
            </div>
          )}
          {meal.priceEstimateEur !== null && (
            <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
              ~{meal.priceEstimateEur} €
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

/** Ration journalière (kcal total si au moins un repas a kcalEstimate) */
function DailyRation({ meals }: { meals: MealModel[] }) {
  const { t } = useTranslation('pilgrimage');
  const totalKcal = meals.reduce<number | null>((acc, m) => {
    if (m.kcalEstimate === null) return acc;
    return (acc ?? 0) + m.kcalEstimate;
  }, null);

  if (totalKcal === null) return null;

  return (
    <div
      data-testid="meal-daily-ration"
      style={{
        fontSize: 'var(--font-size-xs)',
        color: 'var(--color-text-tertiary)',
        marginTop: 'var(--space-2)',
        textAlign: 'right',
      }}
    >
      {t('meal.daily_kcal', { kcal: totalKcal })}
    </div>
  );
}

/** Groupement des repas par moment de la journée */
const MEAL_ORDER: Array<'breakfast' | 'lunch' | 'dinner' | 'snack'> = ['breakfast', 'lunch', 'dinner', 'snack'];

function MealsSection({ meals }: { meals: MealModel[] }) {
  const specialties = meals.filter(m => m.mealContext === 'local_specialty');
  const nonSpecialties = meals.filter(m => m.mealContext !== 'local_specialty');

  // Grouper par type dans l'ordre canonique
  const grouped = MEAL_ORDER.reduce<Record<string, MealModel[]>>((acc, type) => {
    const group = nonSpecialties.filter(m => m.mealType === type);
    if (group.length > 0) acc[type] = group;
    return acc;
  }, {});

  return (
    <div>
      {/* Spécialités locales en tête */}
      {specialties.map(m => <LocalSpecialtyCard key={m.id} meal={m} />)}

      {/* Repas par moment */}
      {Object.entries(grouped).map(([, group]) =>
        group.map(meal => <MealRow key={meal.id} meal={meal} />),
      )}

      <DailyRation meals={meals} />
    </div>
  );
}

// ── Écran principal ───────────────────────────────────────────────────────────

export function StageDetailScreen() {
  const { code } = useParams<{ code: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');

  const { data: stage, isLoading, isError } = useStageDetail(code ?? '');

  const mainGpxTrace = stage?.gpxTraces.find(tr => tr.traceType === 'stage_main');
  const { data: gpxLine } = useGpxSimplified(mainGpxTrace?.id ?? null);

  if (isLoading) {
    return (
      <div style={{ padding: 'var(--space-4)' }}>
        <SkeletonCard count={3} />
      </div>
    );
  }

  if (isError || !stage) {
    return <EmptyState message={t('error.stage_not_found')} />;
  }

  const poiWaypoints = stage.waypoints.filter(w => w.type === 'poi' && w.isActive);
  const waterWaypoints = stage.waypoints.filter(w => w.type === 'water' && w.isActive);
  const primaryAccom = stage.accommodations.filter(a => a.isPrimary);
  const altAccom = stage.accommodations.filter(a => !a.isPrimary);

  // Hébergement avec bivouac légal
  const bivouacAccom = stage.accommodations.find(a => a.bivouacLegal);

  const { hours, minutes } = (() => {
    const h = Math.floor(stage.estimatedDurationH);
    const m = Math.round((stage.estimatedDurationH - h) * 60);
    return { hours: h, minutes: m };
  })();

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
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', gap: 'var(--space-3)' }}>
          <button
            type="button"
            onClick={() => navigate(-1)}
            aria-label={t('stage.back_to_stages')}
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              color: 'var(--color-text-accent)', padding: '8px', borderRadius: 'var(--radius-md)',
              display: 'flex', alignItems: 'center', minWidth: '44px', minHeight: '44px', justifyContent: 'center',
            }}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>
          <div>
            <div style={{ fontSize: 'var(--font-size-md)', fontWeight: 'var(--font-weight-semibold)', color: 'var(--color-text-primary)', fontFamily: 'var(--font-family-interface)' }}>
              {t('stage.day_label', { n: stage.dayNumber })} · {stage.startWaypoint.name} → {stage.endWaypoint.name}
            </div>
            <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
              {t('stage.distance', { x: stage.distanceKm })} ·{' '}
              {t('stage.elevation', { y: stage.elevationGainM })} ·{' '}
              {t('stage.duration', { h: hours, m: String(minutes).padStart(2, '0') })}
            </div>
          </div>
        </div>
      </header>

      {/* Contenu scrollable */}
      <div style={{
        flex: 1,
        overflowY: 'auto',
        WebkitOverflowScrolling: 'touch',
        paddingBottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-4))',
      }}>
        {/* Mini-carte */}
        <Suspense fallback={<div style={{ height: '180px', backgroundColor: 'var(--color-bg-elevated)' }} />}>
          {gpxLine && (
            <div style={{ height: '180px', position: 'relative' }} data-testid="mini-map">
              <MiniMap
                stageCode={stage.code}
                gpxLine={gpxLine}
                waypoints={stage.waypoints}
              />
              <button
                type="button"
                data-testid="btn-see-on-map"
                onClick={() => navigate(`/carte/${stage.code}`)}
                aria-label={t('stage.see_on_map')}
                style={{
                  position: 'absolute',
                  bottom: 'var(--space-3)',
                  right: 'var(--space-3)',
                  backgroundColor: 'var(--color-interactive-primary)',
                  color: 'var(--color-text-inverse)',
                  border: 'none',
                  borderRadius: 'var(--radius-lg)',
                  padding: '8px 14px',
                  fontSize: 'var(--font-size-sm)',
                  fontWeight: 'var(--font-weight-medium)',
                  cursor: 'pointer',
                  zIndex: 10,
                  minHeight: '44px',
                  fontFamily: 'var(--font-family-interface)',
                }}
              >
                {t('stage.see_on_map')}
              </button>
            </div>
          )}
        </Suspense>

        <div style={{ padding: 'var(--space-4)' }}>
          {/* POI */}
          {poiWaypoints.length > 0 && (
            <section
              aria-labelledby="poi-heading"
              data-testid="poi-section"
              style={{ marginBottom: 'var(--space-6)' }}
            >
              <SectionHeader title={t('stage.poi_section')} />
              <div id="poi-heading" style={{ display: 'none' }}>{t('stage.poi_section')}</div>
              {poiWaypoints.map(wp => <PoiItem key={wp.id} wp={wp} />)}
            </section>
          )}

          {/* ULTREIA-25 — Repas */}
          {stage.meals.length > 0 && (
            <section
              aria-labelledby="meals-heading"
              data-testid="meals-section"
              style={{ marginBottom: 'var(--space-6)' }}
            >
              <SectionHeader title={t('stage.meals_section')} />
              <div id="meals-heading" style={{ display: 'none' }}>{t('stage.meals_section')}</div>
              <MealsSection meals={stage.meals} />
            </section>
          )}

          {/* ULTREIA-24 — Hébergement */}
          {(primaryAccom.length > 0 || altAccom.length > 0 || bivouacAccom) && (
            <section
              aria-labelledby="night-heading"
              data-testid="night-section"
              style={{ marginBottom: 'var(--space-6)' }}
            >
              <SectionHeader title={t('stage.night_section')} />
              <div id="night-heading" style={{ display: 'none' }}>{t('stage.night_section')}</div>

              {/* Hébergement principal */}
              {primaryAccom.map(a => <AccommodationCard key={a.id} accom={a} isPrimary />)}

              {/* Zone bivouac légal (si aucun hébergement principal ou en complément) */}
              {bivouacAccom && (
                <BivouacZone notes={bivouacAccom.bivouacNotes} />
              )}

              {/* Alternatives en accordéon */}
              {altAccom.length > 0 && (
                <AltAccommodationsAccordion items={altAccom} />
              )}
            </section>
          )}

          {/* Points d'eau */}
          {waterWaypoints.length > 0 && (
            <section aria-labelledby="water-heading" style={{ marginBottom: 'var(--space-6)' }}>
              <SectionHeader title={t('stage.water_points')} />
              <div id="water-heading" style={{ display: 'none' }}>{t('stage.water_points')}</div>
              {waterWaypoints.map(wp => (
                <div key={wp.id} style={{
                  display: 'flex', alignItems: 'center', gap: '8px',
                  padding: 'var(--space-2) 0',
                  borderBottom: '1px solid var(--color-border-subtle)',
                  fontSize: 'var(--font-size-sm)',
                  color: 'var(--color-text-secondary)',
                }}>
                  <span aria-hidden="true" style={{ color: 'var(--color-water-teal)' }}>💧</span>
                  {wp.name}
                  {wp.description && <span style={{ color: 'var(--color-text-tertiary)' }}> — {wp.description}</span>}
                </div>
              ))}
            </section>
          )}

          {/* Note pratique */}
          {stage.notes && (
            <section aria-labelledby="note-heading" style={{ marginBottom: 'var(--space-6)' }}>
              <SectionHeader title={t('stage.practical_note')} />
              <div id="note-heading" style={{ display: 'none' }}>{t('stage.practical_note')}</div>
              <p style={{
                fontSize: 'var(--font-size-sm)',
                color: 'var(--color-text-secondary)',
                lineHeight: 'var(--line-height-relaxed)',
                margin: 0,
                backgroundColor: 'var(--color-bg-elevated)',
                borderRadius: 'var(--radius-lg)',
                padding: 'var(--space-4)',
                borderLeft: '3px solid var(--color-gold-500)',
              }}>
                {stage.notes}
              </p>
            </section>
          )}
        </div>
      </div>
    </div>
  );
}
