/**
 * DTOs — Pilgrimage API
 * Miroir exact des Laravel API Resources.
 * Ne jamais utiliser ces types dans les composants ou le store.
 * Le mapper transforme ResponseDto → Model UI.
 */

export interface WaypointResponseDto {
  id: string;
  slug: string;
  name: string;
  type: 'city' | 'poi' | 'water' | 'rest' | 'crossroads' | 'bivouac_zone';
  poi_category: string | null;
  latitude: number;
  longitude: number;
  detour_type: 'on_path' | 'short' | 'medium' | 'long' | null;
  detour_distance_km: number | null;
  detour_duration_min: number | null;
  visit_duration_min: number | null;
  entry_cost_eur: number | null;
  booking_required: boolean;
  booking_contact: string | null;
  opening_notes: string | null;
  description: string | null;
  is_active: boolean;
  active_from: string | null;
  active_until: string | null;
}

export interface AccommodationResponseDto {
  id: string;
  name: string;
  type: 'gite' | 'camping' | 'hostel' | 'hotel' | 'abbey' | 'donativo' | 'bivouac';
  address: string | null;
  phone: string | null;
  website: string | null;
  email: string | null;
  price_min_eur: number | null;
  price_max_eur: number | null;
  is_donativo: boolean;
  capacity: number | null;
  has_shower: boolean;
  has_kitchen: boolean;
  has_wifi: boolean;
  stamps_credencial: boolean;
  pilgrim_friendly: boolean;
  booking_required: boolean;
  booking_notice_days: number | null;
  bivouac_legal: boolean;
  bivouac_notes: string | null;
  is_primary: boolean;
  sort_order: number | null;
  notes: string | null;
  verified_at: string | null;
  is_obsolete: boolean;
}

export interface MealResponseDto {
  id: string;
  meal_type: 'breakfast' | 'lunch' | 'dinner' | 'snack';
  name: string;
  description: string | null;
  meal_context: 'restaurant' | 'bivouac_cooking' | 'grocery' | 'local_specialty';
  restaurant_name: string | null;
  restaurant_address: string | null;
  price_estimate_eur: number | null;
  kcal_estimate: number | null;
  weight_g: number | null;
  notes: string | null;
}

export interface GpxTraceResponseDto {
  id: string;
  trace_type: 'stage_main' | 'detour' | 'variant';
  name: string;
  distance_km: number;
  elevation_gain_m: number;
  elevation_loss_m: number;
  track_points_count: number;
}

export interface StageResponseDto {
  id: string;
  code: string;
  name: string;
  day_number: number;
  distance_km: number;
  elevation_gain_m: number;
  elevation_loss_m: number;
  estimated_duration_h: number;
  difficulty: 'easy' | 'moderate' | 'hard';
  accommodation_type_default: string;
  notes: string | null;
  start_waypoint: WaypointResponseDto;
  end_waypoint: WaypointResponseDto;
}

export interface StageDetailResponseDto extends StageResponseDto {
  waypoints: WaypointResponseDto[];
  accommodations: AccommodationResponseDto[];
  meals: MealResponseDto[];
  gpx_traces: GpxTraceResponseDto[];
}

export interface RouteResponseDto {
  id: string;
  slug: string;
  name: string;
  description: string | null;
  country: 'BE' | 'FR' | 'ES';
  total_distance_km: number;
  total_elevation_gain_m: number;
  is_active: boolean;
  stages: StageResponseDto[];
}

export interface ApiListResponseDto<T> {
  data: T[];
  next_cursor: string | null;
  prev_cursor: string | null;
}

export interface GeoJsonFeatureDto {
  type: 'Feature';
  geometry: {
    type: 'LineString';
    coordinates: [number, number][];
  };
  properties: Record<string, unknown>;
}

export interface GeoJsonCollectionDto {
  type: 'FeatureCollection';
  features: GeoJsonFeatureDto[];
}
