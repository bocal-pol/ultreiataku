import { useParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Suspense, lazy } from 'react';
import { useStageDetail } from '../../../shared/hooks/useStages.ts';
import { useGpxSimplified } from '../../../shared/hooks/useGpx.ts';
import { DetourBadge } from '../../../shared/ui/DetourBadge.tsx';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import { EmptyState } from '../../../shared/ui/EmptyState.tsx';
import type { WaypointModel, MealModel, AccommodationModel } from '../../../models/pilgrimage.ts';

const MiniMap = lazy(() => import('../map/MiniMap.tsx'));

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

function MealItem({ meal }: { meal: MealModel }) {
  const { t } = useTranslation('pilgrimage');
  const icons: Record<string, string> = {
    breakfast: '🌅', lunch: '☀️', dinner: '🌙', snack: '🥐',
  };
  const labelKey = `stage.meal_${meal.mealType === 'breakfast' ? 'morning' : meal.mealType === 'lunch' ? 'lunch' : 'dinner'}` as const;

  return (
    <div style={{ padding: 'var(--space-2) 0', borderBottom: '1px solid var(--color-border-subtle)' }}>
      <div style={{ display: 'flex', gap: '8px', alignItems: 'baseline' }}>
        <span aria-hidden="true">{icons[meal.mealType]}</span>
        <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-tertiary)', fontWeight: 'var(--font-weight-medium)', minWidth: '64px' }}>
          {t(labelKey, { defaultValue: meal.mealType })}
        </span>
        <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
          {meal.restaurantName ?? meal.name}
        </span>
      </div>
    </div>
  );
}

function AccommodationItem({ accom }: { accom: AccommodationModel }) {
  const { t } = useTranslation('pilgrimage');

  return (
    <div style={{
      backgroundColor: 'var(--color-bg-elevated)',
      borderRadius: 'var(--radius-lg)',
      border: '1px solid var(--color-border-subtle)',
      padding: 'var(--space-4)',
      marginBottom: 'var(--space-3)',
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
        <div>
          <div style={{ fontWeight: 'var(--font-weight-semibold)', color: 'var(--color-text-primary)', fontSize: 'var(--font-size-md)' }}>
            {accom.name}
          </div>
          <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-tertiary)', marginTop: '2px' }}>
            {t(`accommodation.types.${accom.type}`, { defaultValue: accom.type })}
          </div>
        </div>
        {accom.priceMinEur !== null && (
          <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', textAlign: 'right' }}>
            {accom.priceMinEur === 0 ? t('accommodation.types.donativo') : `${accom.priceMinEur}€`}
          </div>
        )}
      </div>

      <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginTop: 'var(--space-3)' }}>
        {accom.hasShower && <Amenity label={t('accommodation.amenities.shower')} />}
        {accom.hasKitchen && <Amenity label={t('accommodation.amenities.kitchen')} />}
        {accom.hasWifi && <Amenity label={t('accommodation.amenities.wifi')} />}
        {accom.stampsCredencial && <Amenity label={t('accommodation.amenities.stamp')} />}
      </div>

      {accom.capacity !== null && (
        <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: 'var(--space-2)' }}>
          Capacité : {accom.capacity} pèlerins
        </div>
      )}
    </div>
  );
}

function Amenity({ label }: { label: string }) {
  return (
    <span style={{
      fontSize: 'var(--font-size-xs)',
      color: 'var(--color-camp-green)',
      backgroundColor: 'rgba(90,158,90,0.12)',
      borderRadius: 'var(--radius-full)',
      padding: '2px 8px',
    }}>
      ✓ {label}
    </span>
  );
}

export function StageDetailScreen() {
  const { code } = useParams<{ code: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');

  const { data: stage, isLoading, isError } = useStageDetail(code ?? '');

  const mainGpxTrace = stage?.gpxTraces.find(t => t.traceType === 'stage_main');
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
            <div style={{ height: '180px', position: 'relative' }}>
              <MiniMap
                stageCode={stage.code}
                gpxLine={gpxLine}
                waypoints={stage.waypoints}
              />
              <button
                type="button"
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
            <section aria-labelledby="poi-heading" style={{ marginBottom: 'var(--space-6)' }}>
              <SectionHeader title={t('stage.poi_section')} />
              <div id="poi-heading" style={{ display: 'none' }}>{t('stage.poi_section')}</div>
              {poiWaypoints.map(wp => <PoiItem key={wp.id} wp={wp} />)}
            </section>
          )}

          {/* Repas */}
          {stage.meals.length > 0 && (
            <section aria-labelledby="meals-heading" style={{ marginBottom: 'var(--space-6)' }}>
              <SectionHeader title={t('stage.meals_section')} />
              <div id="meals-heading" style={{ display: 'none' }}>{t('stage.meals_section')}</div>
              {stage.meals.map(m => <MealItem key={m.id} meal={m} />)}
            </section>
          )}

          {/* Hébergement */}
          {(primaryAccom.length > 0 || altAccom.length > 0) && (
            <section aria-labelledby="night-heading" style={{ marginBottom: 'var(--space-6)' }}>
              <SectionHeader title={t('stage.night_section')} />
              <div id="night-heading" style={{ display: 'none' }}>{t('stage.night_section')}</div>
              {primaryAccom.map(a => <AccommodationItem key={a.id} accom={a} />)}
              {altAccom.length > 0 && (
                <>
                  <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', margin: 'var(--space-3) 0 var(--space-2)' }}>
                    {t('stage.accommodation_alt')}
                  </div>
                  {altAccom.map(a => <AccommodationItem key={a.id} accom={a} />)}
                </>
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
