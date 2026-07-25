import { useEffect, useRef, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useStageDetail } from '../../../shared/hooks/useStages.ts';
import { useGpxSimplified } from '../../../shared/hooks/useGpx.ts';
import { SkeletonCard } from '../../../shared/ui/SkeletonCard.tsx';
import type { WaypointModel } from '../../../models/pilgrimage.ts';

type GpsState = 'idle' | 'loading' | 'success' | 'denied' | 'unavailable';

type LayerName = 'trail' | 'detours' | 'accommodations' | 'water';

const WAYPOINT_ICONS: Record<string, { color: string; symbol: string }> = {
  poi:           { color: '#c8963c', symbol: '★' },
  water:         { color: '#2da8a8', symbol: '💧' },
  city:          { color: '#e8d9c4', symbol: '●' },
  bivouac_zone:  { color: '#5a9e5a', symbol: '⛺' },
  rest:          { color: '#7d6340', symbol: '·' },
  crossroads:    { color: '#7d6340', symbol: '✕' },
};

function makeMarkerIcon(color: string, symbol: string) {
  return L.divIcon({
    className: '',
    html: `<div aria-hidden="true" style="
      width:32px;height:32px;border-radius:50%;
      background:rgba(26,18,8,0.85);border:2px solid ${color};
      display:flex;align-items:center;justify-content:center;
      font-size:14px;color:${color};
    ">${symbol}</div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 16],
  });
}

export function MapScreen() {
  const { code } = useParams<{ code?: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');

  const { data: stage } = useStageDetail(code ?? '');
  const mainTrace = stage?.gpxTraces.find(t => t.traceType === 'stage_main');
  const { data: gpxLine, isError: gpxError } = useGpxSimplified(mainTrace?.id ?? null);

  const mapContainerRef = useRef<HTMLDivElement>(null);
  const mapRef = useRef<L.Map | null>(null);
  const trailLayerRef = useRef<L.Polyline | null>(null);
  const markersLayerRef = useRef<L.LayerGroup | null>(null);

  const [gpsState, setGpsState] = useState<GpsState>('idle');
  const [gpsMarker, setGpsMarker] = useState<L.CircleMarker | null>(null);
  const [layers, setLayers] = useState<Record<LayerName, boolean>>({
    trail: true, detours: true, accommodations: true, water: true,
  });
  const [showLayerPanel, setShowLayerPanel] = useState(false);
  const [selectedWaypoint, setSelectedWaypoint] = useState<WaypointModel | null>(null);

  // Init carte
  useEffect(() => {
    if (!mapContainerRef.current || mapRef.current) return;

    const map = L.map(mapContainerRef.current, {
      zoomControl: false,
      attributionControl: true,
      center: [50.6, 5.4],
      zoom: 10,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    markersLayerRef.current = L.layerGroup().addTo(map);
    mapRef.current = map;

    return () => {
      map.remove();
      mapRef.current = null;
    };
  }, []);

  // Trace GPX
  useEffect(() => {
    const map = mapRef.current;
    if (!map || !gpxLine || gpxLine.coordinates.length === 0) return;

    if (trailLayerRef.current) map.removeLayer(trailLayerRef.current);

    const latLngs = gpxLine.coordinates.map(([lng, lat]) => [lat, lng] as [number, number]);
    const poly = L.polyline(latLngs, {
      color: '#4a90d9',
      weight: 3,
      opacity: layers.trail ? 1 : 0,
    }).addTo(map);
    trailLayerRef.current = poly;
    map.fitBounds(poly.getBounds(), { padding: [60, 60] });
  }, [gpxLine, layers.trail]);

  // Markers waypoints
  useEffect(() => {
    const group = markersLayerRef.current;
    const map = mapRef.current;
    if (!group || !map || !stage) return;

    group.clearLayers();

    stage.waypoints
      .filter(w => w.isActive)
      .forEach(wp => {
        const hide =
          (wp.type === 'poi' && !layers.trail) ||
          (wp.type === 'water' && !layers.water);
        if (hide) return;

        const cfg = WAYPOINT_ICONS[wp.type] ?? WAYPOINT_ICONS['poi'];
        const color = cfg?.color ?? '#c8963c';
        const symbol = cfg?.symbol ?? '★';
        const icon = makeMarkerIcon(color, symbol);

        L.marker([wp.lat, wp.lng], { icon })
          .addTo(group)
          .on('click', () => setSelectedWaypoint(wp));
      });
  }, [stage, layers]);

  // Visibilité trail
  useEffect(() => {
    if (!trailLayerRef.current) return;
    trailLayerRef.current.setStyle({ opacity: layers.trail ? 1 : 0 });
  }, [layers.trail]);

  // GPS one-shot
  const handleGps = useCallback(() => {
    if (!navigator.geolocation) {
      setGpsState('unavailable');
      return;
    }
    setGpsState('loading');
    navigator.geolocation.getCurrentPosition(
      pos => {
        const map = mapRef.current;
        if (!map) return;
        const { latitude: lat, longitude: lng } = pos.coords;

        if (gpsMarker) map.removeLayer(gpsMarker);
        const marker = L.circleMarker([lat, lng], {
          radius: 8,
          color: '#4a90d9',
          fillColor: '#4a90d9',
          fillOpacity: 0.8,
        }).addTo(map);
        setGpsMarker(marker);
        map.setView([lat, lng], 14);
        setGpsState('success');
      },
      err => {
        setGpsState(err.code === GeolocationPositionError.PERMISSION_DENIED ? 'denied' : 'unavailable');
      },
      { enableHighAccuracy: true, timeout: 10000 },
    );
  }, [gpsMarker]);

  const toggleLayer = (name: LayerName) => {
    setLayers(prev => ({ ...prev, [name]: !prev[name] }));
  };

  if (code && !stage) {
    return (
      <div style={{ padding: 'var(--space-4)' }}>
        <SkeletonCard count={1} />
      </div>
    );
  }

  const layerKeys: LayerName[] = ['trail', 'detours', 'accommodations', 'water'];

  return (
    <div style={{ position: 'relative', width: '100%', height: '100%', backgroundColor: 'var(--color-bg-map)' }}>
      {/* Carte */}
      <div
        ref={mapContainerRef}
        aria-label="Carte interactive de l'étape"
        style={{ width: '100%', height: '100%' }}
      />

      {/* Overlay header */}
      <div style={{
        position: 'absolute', top: 0, left: 0, right: 0,
        height: 'var(--map-header-height, 44px)',
        backgroundColor: 'var(--map-header-bg)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: '0 var(--space-4)',
        zIndex: 1000,
      }}>
        <button
          type="button"
          onClick={() => navigate(code ? `/etapes/${code}` : -1 as unknown as string)}
          aria-label={stage ? t('map.back', { n: stage.dayNumber }) : 'Retour'}
          style={{
            background: 'none', border: 'none', cursor: 'pointer',
            color: 'var(--color-text-primary)', display: 'flex', alignItems: 'center',
            minWidth: '44px', minHeight: '44px', padding: '8px',
          }}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
        </button>

        {stage && (
          <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-primary)', fontWeight: 'var(--font-weight-medium)' }}>
            {t('stage.day_label', { n: stage.dayNumber })} · {stage.startWaypoint.name} → {stage.endWaypoint.name}
          </span>
        )}

        <button
          type="button"
          onClick={() => setShowLayerPanel(p => !p)}
          aria-label={t('map.toggle_layers')}
          aria-expanded={showLayerPanel}
          style={{
            background: 'none', border: 'none', cursor: 'pointer',
            color: 'var(--color-text-primary)', display: 'flex', alignItems: 'center',
            minWidth: '44px', minHeight: '44px', padding: '8px',
          }}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
          </svg>
        </button>
      </div>

      {/* Panel layers */}
      {showLayerPanel && (
        <div
          role="dialog"
          aria-label={t('map.toggle_layers')}
          style={{
            position: 'absolute', top: '44px', right: 'var(--space-4)',
            backgroundColor: 'var(--color-bg-elevated)',
            borderRadius: 'var(--radius-lg)',
            border: '1px solid var(--color-border-subtle)',
            padding: 'var(--space-3)',
            zIndex: 1001,
            minWidth: '180px',
            boxShadow: 'var(--shadow-lg)',
          }}
        >
          {layerKeys.map(key => (
            <label
              key={key}
              style={{
                display: 'flex', alignItems: 'center', gap: '8px',
                minHeight: '44px', cursor: 'pointer',
                fontSize: 'var(--font-size-sm)', color: 'var(--color-text-primary)',
              }}
            >
              <input
                type="checkbox"
                checked={layers[key]}
                onChange={() => toggleLayer(key)}
                style={{ accentColor: 'var(--color-gold-500)', width: '18px', height: '18px' }}
              />
              {t(`map.layer_${key}` as `map.layer_${LayerName}`)}
            </label>
          ))}
        </div>
      )}

      {/* Contrôles zoom + GPS */}
      <div style={{
        position: 'absolute', right: 'var(--space-4)',
        bottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-16))',
        display: 'flex', flexDirection: 'column', gap: 'var(--space-2)',
        zIndex: 1000,
      }}>
        {(['zoomIn', 'zoomOut'] as const).map(action => (
          <button
            key={action}
            type="button"
            onClick={() => { if (mapRef.current) { if (action === 'zoomIn') { mapRef.current.zoomIn(); } else { mapRef.current.zoomOut(); } } }}
            aria-label={action === 'zoomIn' ? 'Zoom avant' : 'Zoom arrière'}
            style={{
              width: 'var(--map-control-size)', height: 'var(--map-control-size)',
              backgroundColor: 'var(--map-control-bg)',
              border: '1px solid var(--color-border-subtle)',
              borderRadius: 'var(--radius-md)',
              color: 'var(--color-text-primary)',
              cursor: 'pointer', fontSize: '18px',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              boxShadow: 'var(--shadow-md)',
            }}
          >
            {action === 'zoomIn' ? '+' : '−'}
          </button>
        ))}

        <button
          type="button"
          onClick={handleGps}
          aria-label={t('map.center_gps')}
          aria-busy={gpsState === 'loading'}
          style={{
            width: 'var(--map-control-size)', height: 'var(--map-control-size)',
            backgroundColor: gpsState === 'success' ? 'var(--color-interactive-primary)' : 'var(--map-control-bg)',
            border: '1px solid var(--color-border-subtle)',
            borderRadius: 'var(--radius-md)',
            color: gpsState === 'success' ? 'var(--color-text-inverse)' : 'var(--color-text-primary)',
            cursor: 'pointer',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            boxShadow: 'var(--shadow-md)',
          }}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M1 12h4M19 12h4"/>
          </svg>
        </button>
      </div>

      {/* Feedback GPS */}
      {(gpsState === 'denied' || gpsState === 'unavailable') && (
        <div role="alert" style={{
          position: 'absolute', bottom: 'calc(var(--nav-height) + var(--nav-safe-area) + var(--space-4))',
          left: 'var(--space-4)', right: 'var(--space-4)',
          backgroundColor: 'var(--color-bg-elevated)',
          borderRadius: 'var(--radius-lg)',
          border: '1px solid var(--color-border-subtle)',
          padding: 'var(--space-3) var(--space-4)',
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-text-secondary)',
          zIndex: 1000,
          boxShadow: 'var(--shadow-md)',
        }}>
          {gpsState === 'denied' ? t('map.gps_permission') : t('map.gps_unavailable')}
        </div>
      )}

      {/* Erreur GPX */}
      {gpxError && (
        <div role="alert" style={{
          position: 'absolute', top: '52px', left: 'var(--space-4)', right: 'var(--space-4)',
          backgroundColor: 'rgba(232,152,58,0.15)',
          borderRadius: 'var(--radius-md)',
          padding: 'var(--space-3)',
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-detour-amber)',
          zIndex: 1000,
        }}>
          {t('error.gpx_unavailable')}
        </div>
      )}

      {/* Overlay footer + navigation étapes */}
      {stage && (
        <div style={{
          position: 'absolute', bottom: 'calc(var(--nav-height) + var(--nav-safe-area))',
          left: 0, right: 0,
          backgroundColor: 'var(--map-footer-bg)',
          minHeight: '56px',
          display: 'flex', alignItems: 'center', justifyContent: 'space-between',
          padding: '0 var(--space-4)',
          zIndex: 1000,
        }}>
          <span style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-primary)' }}>
            {t('map.overlay_title', {
              n: stage.dayNumber,
              from: stage.startWaypoint.name,
              to: stage.endWaypoint.name,
              x: stage.distanceKm,
            })}
          </span>
        </div>
      )}

      {/* Offline tiles notice */}
      {!navigator.onLine && (
        <div role="status" style={{
          position: 'absolute', top: '52px',
          left: 'var(--space-4)', right: 'var(--space-4)',
          backgroundColor: 'rgba(26,18,8,0.9)',
          borderRadius: 'var(--radius-md)',
          padding: 'var(--space-2) var(--space-3)',
          fontSize: 'var(--font-size-xs)',
          color: 'var(--color-text-secondary)',
          zIndex: 1000,
        }}>
          {t('map.offline_tiles')}
        </div>
      )}

      {/* Bottom sheet POI */}
      {selectedWaypoint && (
        <div
          role="dialog"
          aria-label={selectedWaypoint.name}
          aria-modal="true"
          style={{
            position: 'absolute', bottom: 'calc(var(--nav-height) + var(--nav-safe-area) + 56px)',
            left: 0, right: 0,
            backgroundColor: 'var(--color-bg-sheet)',
            borderRadius: 'var(--radius-xl) var(--radius-xl) 0 0',
            borderTop: '1px solid var(--color-border-subtle)',
            padding: 'var(--space-6)',
            zIndex: 1100,
            maxHeight: '50vh',
            overflowY: 'auto',
            boxShadow: 'var(--shadow-lg)',
          }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 'var(--space-4)' }}>
            <div>
              <div style={{ fontSize: 'var(--font-size-lg)', fontWeight: 'var(--font-weight-semibold)', color: 'var(--color-text-accent)' }}>
                {selectedWaypoint.name}
              </div>
              {selectedWaypoint.poiCategory && (
                <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', marginTop: '2px' }}>
                  {t(`poi.categories.${selectedWaypoint.poiCategory}` as Parameters<typeof t>[0], { defaultValue: selectedWaypoint.poiCategory })}
                </div>
              )}
            </div>
            <button
              type="button"
              onClick={() => setSelectedWaypoint(null)}
              aria-label={t('poi.close')}
              style={{
                background: 'none', border: 'none', cursor: 'pointer',
                color: 'var(--color-text-tertiary)', padding: '8px',
                minWidth: '44px', minHeight: '44px', borderRadius: 'var(--radius-md)',
              }}
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          {selectedWaypoint.detourType && (
            <div style={{ marginBottom: 'var(--space-3)' }}>
              {selectedWaypoint.detourDistanceKm && selectedWaypoint.detourDurationMin !== null && selectedWaypoint.visitDurationMin !== null && (
                <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', marginBottom: '4px' }}>
                  {t('poi.detour_format', {
                    km: selectedWaypoint.detourDistanceKm,
                    walk: selectedWaypoint.detourDurationMin,
                    visit: selectedWaypoint.visitDurationMin,
                  })}
                </div>
              )}
              {selectedWaypoint.entryCostEur !== null && selectedWaypoint.entryCostEur > 0 && (
                <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}>
                  {t('poi.cost_format', { cost: selectedWaypoint.entryCostEur })}
                </div>
              )}
              {selectedWaypoint.bookingRequired && (
                <div style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-detour-amber)', marginTop: '4px' }}>
                  {t('poi.booking_required')}
                </div>
              )}
            </div>
          )}

          {selectedWaypoint.bookingContact && (
            <a
              href={selectedWaypoint.bookingContact.includes('@') ? `mailto:${selectedWaypoint.bookingContact}` : `tel:${selectedWaypoint.bookingContact}`}
              style={{ display: 'block', color: 'var(--color-text-accent)', fontSize: 'var(--font-size-sm)', minHeight: '44px', lineHeight: '44px', marginBottom: 'var(--space-2)' }}
            >
              {selectedWaypoint.bookingContact}
            </a>
          )}

          {selectedWaypoint.description && (
            <p style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', lineHeight: 'var(--line-height-relaxed)', margin: 0 }}>
              {selectedWaypoint.description}
            </p>
          )}
        </div>
      )}

      {/* Backdrop sheet */}
      {selectedWaypoint && (
        <div
          aria-hidden="true"
          onClick={() => setSelectedWaypoint(null)}
          style={{
            position: 'absolute', inset: 0, backgroundColor: 'var(--sheet-backdrop)',
            zIndex: 1050,
          }}
        />
      )}
    </div>
  );
}
