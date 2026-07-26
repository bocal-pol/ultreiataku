/**
 * DTOs — Pack (Sac) API — Vague 1d
 * Miroir exact des Laravel API Resources.
 * Ne jamais utiliser ces types dans les composants ou le store.
 */

export type PackCategory =
  | 'portage'
  | 'sleeping'
  | 'cooking'
  | 'water'
  | 'clothing'
  | 'hygiene'
  | 'health'
  | 'navigation'
  | 'misc';

export type PackSeason = 'spring' | 'summer' | 'autumn' | 'winter';

export type PackConfiguration = 'solo' | 'duo';

export type WeightIndicator = 'ok' | 'warn' | 'over';

export interface PackItemResponseDto {
  id: string;
  pack_scenario_id: string;
  name: string;
  category: PackCategory;
  category_label: string;
  brand: string | null;
  model: string | null;
  weight_g: number;
  is_shared: boolean;
  is_consumable: boolean;
  replacement_km: number | null;
  notes: string | null;
  sort_order: number | null;
  created_at: string;
}

export interface PackScenarioResponseDto {
  id: string;
  pilgrim_id: string;
  name: string;
  description: string | null;
  target_base_weight_kg: number | null;
  configuration: PackConfiguration | null;
  season: PackSeason | null;
  season_label: string | null;
  base_weight_g: number;
  base_weight_kg: number;
  total_weight_g: number;
  total_weight_kg: number;
  weight_indicator: WeightIndicator;
  items?: PackItemResponseDto[];
  items_count?: number;
  created_at: string;
}

export interface PackScenariosListResponseDto {
  data: PackScenarioResponseDto[];
}

export interface PackScenarioDetailResponseDto {
  data: PackScenarioResponseDto;
}
