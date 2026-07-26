/**
 * Mappers — DTO → Model UI
 * Aucun DTO ne franchit cette frontière vers les composants.
 */

import type {
  WaypointResponseDto,
  AccommodationResponseDto,
  MealResponseDto,
  GpxTraceResponseDto,
  StageResponseDto,
  StageDetailResponseDto,
  RouteResponseDto,
  GeoJsonCollectionDto,
} from '../dtos/pilgrimage.ts';
import type {
  WaypointModel,
  AccommodationModel,
  MealModel,
  GpxTraceModel,
  StageModel,
  StageDetailModel,
  RouteModel,
  GpxLineModel,
} from '../models/pilgrimage.ts';

export function mapWaypoint(dto: WaypointResponseDto): WaypointModel {
  return {
    id: dto.id,
    slug: dto.slug,
    name: dto.name,
    type: dto.type,
    poiCategory: dto.poi_category,
    lat: dto.latitude,
    lng: dto.longitude,
    detourType: dto.detour_type,
    detourDistanceKm: dto.detour_distance_km,
    detourDurationMin: dto.detour_duration_min,
    visitDurationMin: dto.visit_duration_min,
    entryCostEur: dto.entry_cost_eur,
    bookingRequired: dto.booking_required,
    bookingContact: dto.booking_contact,
    openingNotes: dto.opening_notes,
    description: dto.description,
    isActive: dto.is_active,
  };
}

export function mapAccommodation(dto: AccommodationResponseDto): AccommodationModel {
  return {
    id: dto.id,
    name: dto.name,
    type: dto.type,
    address: dto.address,
    phone: dto.phone,
    website: dto.website,
    email: dto.email,
    priceMinEur: dto.price_min_eur,
    priceMaxEur: dto.price_max_eur,
    isDonativo: dto.is_donativo,
    capacity: dto.capacity,
    hasShower: dto.has_shower,
    hasKitchen: dto.has_kitchen,
    hasWifi: dto.has_wifi,
    stampsCredencial: dto.stamps_credencial,
    bookingRequired: dto.booking_required,
    bookingNoticeDays: dto.booking_notice_days,
    bivouacLegal: dto.bivouac_legal,
    bivouacNotes: dto.bivouac_notes,
    isPrimary: dto.is_primary,
    notes: dto.notes,
    verifiedAt: dto.verified_at,
    isObsolete: dto.is_obsolete,
  };
}

export function mapMeal(dto: MealResponseDto): MealModel {
  return {
    id: dto.id,
    mealType: dto.meal_type,
    name: dto.name,
    description: dto.description,
    mealContext: dto.meal_context,
    restaurantName: dto.restaurant_name,
    restaurantAddress: dto.restaurant_address,
    priceEstimateEur: dto.price_estimate_eur,
    kcalEstimate: dto.kcal_estimate,
    weightG: dto.weight_g,
    notes: dto.notes,
  };
}

export function mapGpxTrace(dto: GpxTraceResponseDto): GpxTraceModel {
  return {
    id: dto.id,
    traceType: dto.trace_type,
    name: dto.name,
    distanceKm: dto.distance_km,
    elevationGainM: dto.elevation_gain_m,
    trackPointsCount: dto.track_points_count,
  };
}

export function mapStage(dto: StageResponseDto): StageModel {
  return {
    id: dto.id,
    code: dto.code,
    name: dto.name,
    dayNumber: dto.day_number,
    distanceKm: dto.distance_km,
    elevationGainM: dto.elevation_gain_m,
    elevationLossM: dto.elevation_loss_m,
    estimatedDurationH: dto.estimated_duration_h,
    difficulty: dto.difficulty,
    accommodationTypeDefault: dto.accommodation_type_default,
    notes: dto.notes,
    startWaypoint: mapWaypoint(dto.start_waypoint),
    endWaypoint: mapWaypoint(dto.end_waypoint),
  };
}

export function mapStageDetail(dto: StageDetailResponseDto): StageDetailModel {
  return {
    ...mapStage(dto),
    waypoints: dto.waypoints.map(mapWaypoint),
    accommodations: dto.accommodations.map(mapAccommodation),
    meals: dto.meals.map(mapMeal),
    gpxTraces: dto.gpx_traces.map(mapGpxTrace),
  };
}

export function mapRoute(dto: RouteResponseDto): RouteModel {
  return {
    id: dto.id,
    slug: dto.slug,
    name: dto.name,
    country: dto.country,
    totalDistanceKm: dto.total_distance_km,
    totalElevationGainM: dto.total_elevation_gain_m,
    stages: dto.stages.map(mapStage),
  };
}

export function mapGeoJson(dto: GeoJsonCollectionDto): GpxLineModel {
  const feature = dto.features[0];
  if (!feature) return { coordinates: [] };
  return {
    coordinates: feature.geometry.coordinates as [number, number][],
  };
}
