/**
 * Models UI — Pack (Sac)
 * Types consommés par les composants React.
 * Jamais de DTO brut ici.
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

export interface PackItemModel {
  id: string;
  packScenarioId: string;
  name: string;
  category: PackCategory;
  categoryLabel: string;
  brand: string | null;
  model: string | null;
  weightG: number;
  isShared: boolean;
  isConsumable: boolean;
  replacementKm: number | null;
  notes: string | null;
  sortOrder: number | null;
  createdAt: string;
}

export interface PackScenarioModel {
  id: string;
  pilgrimId: string;
  name: string;
  description: string | null;
  targetBaseWeightKg: number | null;
  configuration: PackConfiguration | null;
  season: PackSeason | null;
  seasonLabel: string | null;
  baseWeightG: number;
  baseWeightKg: number;
  totalWeightG: number;
  totalWeightKg: number;
  weightIndicator: WeightIndicator;
  items: PackItemModel[];
  itemsCount: number;
  createdAt: string;
}

/**
 * RG-01 — Recalcule l'indicateur de poids côté frontend (pour les tests).
 * Vert : base <= target
 * Orange : base > target ET base <= target + 1 kg
 * Rouge : base > target + 1 kg
 */
export function computeWeightIndicator(
  baseWeightKg: number,
  targetBaseWeightKg: number | null,
): WeightIndicator {
  if (targetBaseWeightKg === null) return 'ok';
  if (baseWeightKg <= targetBaseWeightKg) return 'ok';
  if (baseWeightKg <= targetBaseWeightKg + 1) return 'warn';
  return 'over';
}
