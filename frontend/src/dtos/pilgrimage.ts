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
  route_id: string;
  /** Nom de la route (localisé) — exposé quand la relation route est chargée */
  route_name: string | null;
  code: string;
  name: string;
  day_number: number;
  /** FIX-API-001 : champ présent dans StageResource — manquait du DTO frontend */
  is_variant: boolean;
  /** FIX-API-001 : champ présent dans StageResource — manquait du DTO frontend */
  parent_stage_id: string | null;
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

// ─── Vague 1c — Trips & Auth ────────────────────────────────────────────────

export interface PilgrimResponseDto {
  id: string;
  user_id: number;
  display_name: string;
  avatar_url: string | null;
  preferred_locale: 'fr' | 'nl' | 'de';
  configuration: 'solo' | 'duo';
}

export interface TripMemberResponseDto {
  pilgrim: PilgrimResponseDto;
  role: 'organizer' | 'participant' | 'observer';
  joined_at: string;
}

export interface TripResponseDto {
  id: string;
  name: string;
  description: string | null;
  status: 'planned' | 'active' | 'completed' | 'cancelled';
  estimated_start_date: string | null;
  estimated_end_date: string | null;
  configuration: 'solo' | 'duo' | 'group';
  is_public: boolean;
  invite_token: string | null;
  route: RouteResponseDto;
  members: TripMemberResponseDto[];
  organizer: PilgrimResponseDto;
  created_at: string;
}

export interface DepartureResponseDto {
  id: string;
  trip_id: string;
  pilgrim_id: string;
  start_stage_id: string;
  end_stage_id: string;
  planned_start_date: string;
  planned_end_date: string | null;
  status: 'planned' | 'active' | 'paused' | 'completed' | 'abandoned';
}

export interface OccupancyResponseDto {
  accommodation_id: string;
  date: string;
  trip_id: string;
  count: number;
}

export interface TripCreateRequestDto {
  name: string;
  route_id: string;
  estimated_start_date: string | null;
  configuration: 'solo' | 'duo' | 'group';
  description: string | null;
}

export interface DepartureCreateRequestDto {
  start_stage_id: string;
  end_stage_id: string;
  planned_start_date: string;
}

export interface MeResponseDto {
  user: {
    id: number;
    name: string;
    email: string;
  };
  pilgrim: PilgrimResponseDto;
}

export interface InviteTokenResponseDto {
  token: string;
}

export interface TripJoinPreviewResponseDto {
  trip: TripResponseDto;
  role: 'organizer' | 'participant' | 'observer';
}

// ─── Guides ────────────────────────────────────────────────────────────────────────────────
export interface GuideListItemResponseDto {
  slug: string;
  category: string;
  title: string;
  icon: string;
}

export interface GuideDetailResponseDto {
  slug: string;
  category: string;
  title: string;
  icon: string;
  /** Contenu Markdown brut — rendu côté client par MarkdownRenderer */
  content: string;
}

export interface GuideListResponseDto {
  // Groupé par catégorie côté backend : { "Le Corps": [...], "Pratique": [...] }
  data: Record<string, GuideListItemResponseDto[]>;
}
