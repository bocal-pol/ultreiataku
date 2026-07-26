/**
 * Mappers — Pack DTO → Model UI
 * Aucun DTO ne franchit cette frontière vers les composants.
 */

import type { PackItemResponseDto, PackScenarioResponseDto } from '../dtos/pack.ts';
import type { PackItemModel, PackScenarioModel } from '../models/pack.ts';

export function mapPackItem(dto: PackItemResponseDto): PackItemModel {
  return {
    id: dto.id,
    packScenarioId: dto.pack_scenario_id,
    name: dto.name,
    category: dto.category,
    categoryLabel: dto.category_label,
    brand: dto.brand,
    model: dto.model,
    weightG: dto.weight_g,
    isShared: dto.is_shared,
    isConsumable: dto.is_consumable,
    replacementKm: dto.replacement_km,
    notes: dto.notes,
    sortOrder: dto.sort_order,
    createdAt: dto.created_at,
  };
}

export function mapPackScenario(dto: PackScenarioResponseDto): PackScenarioModel {
  return {
    id: dto.id,
    pilgrimId: dto.pilgrim_id,
    name: dto.name,
    description: dto.description,
    targetBaseWeightKg: dto.target_base_weight_kg,
    configuration: dto.configuration,
    season: dto.season,
    seasonLabel: dto.season_label,
    baseWeightG: dto.base_weight_g,
    baseWeightKg: dto.base_weight_kg,
    totalWeightG: dto.total_weight_g,
    totalWeightKg: dto.total_weight_kg,
    weightIndicator: dto.weight_indicator,
    items: (dto.items ?? []).map(mapPackItem),
    itemsCount: dto.items_count ?? dto.items?.length ?? 0,
    createdAt: dto.created_at,
  };
}
