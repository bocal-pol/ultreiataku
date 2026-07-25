import { useEffect, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import type { GpxLineModel, WaypointModel } from '../../../models/pilgrimage.ts';

interface MiniMapProps {
  stageCode: string;
  gpxLine: GpxLineModel;
  waypoints: WaypointModel[];
}

export default function MiniMap({ stageCode, gpxLine, waypoints }: MiniMapProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const mapRef = useRef<L.Map | null>(null);

  useEffect(() => {
    if (!containerRef.current || mapRef.current) return;

    const map = L.map(containerRef.current, {
      zoomControl: false,
      attributionControl: false,
      dragging: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      touchZoom: false,
      keyboard: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '© OSM',
    }).addTo(map);

    if (gpxLine.coordinates.length > 0) {
      // Leaflet : [lat, lng] — GeoJSON est [lng, lat]
      const latLngs = gpxLine.coordinates.map(([lng, lat]) => [lat, lng] as [number, number]);
      const polyline = L.polyline(latLngs, {
        color: '#4a90d9',
        weight: 3,
        opacity: 0.9,
      }).addTo(map);
      map.fitBounds(polyline.getBounds(), { padding: [12, 12] });
    }

    // Marqueurs waypoints actifs
    const poiIcon = L.divIcon({
      className: '',
      html: '<span aria-hidden="true" style="color:#c8963c;font-size:14px;">★</span>',
      iconSize: [16, 16],
      iconAnchor: [8, 8],
    });
    waypoints
      .filter(w => w.isActive && w.type === 'poi')
      .forEach(wp => {
        L.marker([wp.lat, wp.lng], { icon: poiIcon })
          .bindPopup(wp.name)
          .addTo(map);
      });

    mapRef.current = map;

    return () => {
      map.remove();
      mapRef.current = null;
    };
  // stageCode changes -> new map
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [stageCode]);

  // Mise à jour trace sans recréer la carte
  useEffect(() => {
    const map = mapRef.current;
    if (!map || gpxLine.coordinates.length === 0) return;
    const latLngs = gpxLine.coordinates.map(([lng, lat]) => [lat, lng] as [number, number]);
    const poly = L.polyline(latLngs, { color: '#4a90d9', weight: 3 }).addTo(map);
    map.fitBounds(poly.getBounds(), { padding: [12, 12] });
    return () => { map.removeLayer(poly); };
  }, [gpxLine]);

  return (
    <div
      ref={containerRef}
      aria-label={`Mini-carte de l'étape ${stageCode}`}
      style={{ width: '100%', height: '100%' }}
    />
  );
}
