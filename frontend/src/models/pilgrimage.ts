/**
 * Models UI — Pilgrimage
 * Types consommés par les composants React.
 * Jamais de DTO brut ici.
 */

export interface WaypointModel {
  id: string;
  slug: string;
  name: string;
  type: 'city' | 'poi' | 'water' | 'rest' | 'crossroads' | 'bivouac_zone';
  poiCategory: string | null;
  lat: number;
  lng: number;
  detourType: 'on_path' | 'short' | 'medium' | 'long' | null;
  detourDistanceKm: number | null;
  detourDurationMin: number | null;
  visitDurationMin: number | null;
  entryCostEur: number | null;
  bookingRequired: boolean;
  bookingContact: string | null;
  openingNotes: string | null;
  description: string | null;
  isActive: boolean;
}

export interface AccommodationModel {
  id: string;
  name: string;
  type: 'gite' | 'camping' | 'hostel' | 'hotel' | 'abbey' | 'donativo' | 'bivouac';
  address: string | null;
  phone: string | null;
  website: string | null;
  email: string | null;
  priceMinEur: number | null;
  priceMaxEur: number | null;
  isDonativo: boolean;
  capacity: number | null;
  hasShower: boolean;
  hasKitchen: boolean;
  hasWifi: boolean;
  stampsCredencial: boolean;
  bookingRequired: boolean;
  bookingNoticeDays: number | null;
  bivouacLegal: boolean;
  bivouacNotes: string | null;
  isPrimary: boolean;
  notes: string | null;
  /** ISO 8601 date string or null */
  verifiedAt: string | null;
  isObsolete: boolean;
}

export interface MealModel {
  id: string;
  mealType: 'breakfast' | 'lunch' | 'dinner' | 'snack';
  name: string;
  description: string | null;
  mealContext: 'restaurant' | 'bivouac_cooking' | 'grocery' | 'local_specialty';
  restaurantName: string | null;
  restaurantAddress: string | null;
  priceEstimateEur: number | null;
  kcalEstimate: number | null;
  weightG: number | null;
  notes: string | null;
}

export interface GpxTraceModel {
  id: string;
  traceType: 'stage_main' | 'detour' | 'variant';
  name: string;
  distanceKm: number;
  elevationGainM: number;
  trackPointsCount: number;
}

export interface StageModel {
  id: string;
  code: string;
  name: string;
  dayNumber: number;
  distanceKm: number;
  elevationGainM: number;
  elevationLossM: number;
  estimatedDurationH: number;
  difficulty: 'easy' | 'moderate' | 'hard';
  accommodationTypeDefault: string;
  notes: string | null;
  startWaypoint: WaypointModel;
  endWaypoint: WaypointModel;
}

export interface StageDetailModel extends StageModel {
  waypoints: WaypointModel[];
  accommodations: AccommodationModel[];
  meals: MealModel[];
  gpxTraces: GpxTraceModel[];
}

export interface RouteModel {
  id: string;
  slug: string;
  name: string;
  country: 'BE' | 'FR' | 'ES';
  totalDistanceKm: number;
  totalElevationGainM: number;
  stages: StageModel[];
}

export interface GpxLineModel {
  coordinates: [number, number][];
}

/** Statut connectivité réseau */
export type NetworkStatus = 'online' | 'offline';
