import { describe, it, expect } from 'vitest';
import { mapStage, mapWaypoint, mapGeoJson } from '../mappers/pilgrimage.ts';
import type { StageResponseDto, WaypointResponseDto, GeoJsonCollectionDto } from '../dtos/pilgrimage.ts';

const waypointDto: WaypointResponseDto = {
  id: 'wp-1',
  slug: 'liege',
  name: 'Liège',
  type: 'city',
  poi_category: null,
  latitude: 50.645,
  longitude: 5.573,
  detour_type: null,
  detour_distance_km: null,
  detour_duration_min: null,
  visit_duration_min: null,
  entry_cost_eur: null,
  booking_required: false,
  booking_contact: null,
  opening_notes: null,
  description: null,
  is_active: true,
  active_from: null,
  active_until: null,
};

const stageDto: StageResponseDto = {
  id: 'stage-1',
  code: 'BE-01',
  name: 'Liège — Amay',
  day_number: 1,
  distance_km: 22.0,
  elevation_gain_m: 250,
  elevation_loss_m: 200,
  estimated_duration_h: 5.5,
  difficulty: 'easy',
  accommodation_type_default: 'gite',
  notes: null,
  start_waypoint: waypointDto,
  end_waypoint: { ...waypointDto, id: 'wp-2', slug: 'amay', name: 'Amay', latitude: 50.55, longitude: 5.32 },
};

describe('mapWaypoint', () => {
  it('transforme snake_case → camelCase', () => {
    const model = mapWaypoint(waypointDto);
    expect(model.lat).toBe(50.645);
    expect(model.lng).toBe(5.573);
    expect(model.poiCategory).toBeNull();
    expect(model.detourType).toBeNull();
    expect(model.isActive).toBe(true);
  });
});

describe('mapStage', () => {
  it('expose les champs camelCase sans DTO', () => {
    const model = mapStage(stageDto);
    expect(model.code).toBe('BE-01');
    expect(model.dayNumber).toBe(1);
    expect(model.distanceKm).toBe(22.0);
    expect(model.estimatedDurationH).toBe(5.5);
    expect(model.startWaypoint.name).toBe('Liège');
    expect(model.endWaypoint.name).toBe('Amay');
    expect(model.accommodationTypeDefault).toBe('gite');
  });

  it('préserve la difficulté', () => {
    const model = mapStage(stageDto);
    expect(model.difficulty).toBe('easy');
  });
});

describe('mapGeoJson', () => {
  it('retourne les coordonnées du premier Feature', () => {
    const dto: GeoJsonCollectionDto = {
      type: 'FeatureCollection',
      features: [{
        type: 'Feature',
        geometry: { type: 'LineString', coordinates: [[5.57, 50.64], [5.32, 50.55]] },
        properties: {},
      }],
    };
    const model = mapGeoJson(dto);
    expect(model.coordinates).toHaveLength(2);
    expect(model.coordinates[0]).toEqual([5.57, 50.64]);
  });

  it('retourne des coordonnées vides si pas de features', () => {
    const dto: GeoJsonCollectionDto = { type: 'FeatureCollection', features: [] };
    const model = mapGeoJson(dto);
    expect(model.coordinates).toHaveLength(0);
  });
});
