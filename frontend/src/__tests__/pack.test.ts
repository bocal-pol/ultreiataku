/**
 * Tests — Pack (Sac) — Vague 1d
 * - Jauge de poids RG-01 (3 couleurs)
 * - Mapper PackScenario
 * - computeWeightIndicator
 */

import { describe, it, expect } from 'vitest';
import { computeWeightIndicator } from '../models/pack.ts';
import { mapPackItem, mapPackScenario } from '../mappers/pack.ts';
import type { PackItemResponseDto, PackScenarioResponseDto } from '../dtos/pack.ts';

// ─── RG-01 — Jauge de poids ───────────────────────────────────────────────────

describe('computeWeightIndicator (RG-01)', () => {
  it('retourne "ok" quand base <= target', () => {
    expect(computeWeightIndicator(7.0, 8.0)).toBe('ok');
    expect(computeWeightIndicator(8.0, 8.0)).toBe('ok');
  });

  it('retourne "warn" quand base > target ET base <= target + 1', () => {
    expect(computeWeightIndicator(8.5, 8.0)).toBe('warn');
    expect(computeWeightIndicator(9.0, 8.0)).toBe('warn');
  });

  it('retourne "over" quand base > target + 1', () => {
    expect(computeWeightIndicator(9.01, 8.0)).toBe('over');
    expect(computeWeightIndicator(12.0, 8.0)).toBe('over');
  });

  it('retourne "ok" si pas de target (null)', () => {
    expect(computeWeightIndicator(15.0, null)).toBe('ok');
  });

  it('cas limite exact à target + 1 = warn (pas over)', () => {
    expect(computeWeightIndicator(9.0, 8.0)).toBe('warn');
  });

  it('cas limite juste au-dessus de target + 1 = over', () => {
    expect(computeWeightIndicator(9.001, 8.0)).toBe('over');
  });
});

// ─── Mapper PackItem ──────────────────────────────────────────────────────────

describe('mapPackItem', () => {
  const dto: PackItemResponseDto = {
    id: 'item-1',
    pack_scenario_id: 'sc-1',
    name: 'Sac de couchage',
    category: 'sleeping',
    category_label: 'Couchage',
    brand: 'Cumulus',
    model: 'Panyam 450',
    weight_g: 800,
    is_shared: false,
    is_consumable: false,
    replacement_km: 5000,
    notes: 'Excellent rapport qualité/prix',
    sort_order: 1,
    created_at: '2026-01-01T00:00:00Z',
  };

  it('convertit correctement le DTO en Model', () => {
    const model = mapPackItem(dto);
    expect(model.id).toBe('item-1');
    expect(model.packScenarioId).toBe('sc-1');
    expect(model.name).toBe('Sac de couchage');
    expect(model.category).toBe('sleeping');
    expect(model.categoryLabel).toBe('Couchage');
    expect(model.brand).toBe('Cumulus');
    expect(model.model).toBe('Panyam 450');
    expect(model.weightG).toBe(800);
    expect(model.isShared).toBe(false);
    expect(model.isConsumable).toBe(false);
    expect(model.replacementKm).toBe(5000);
    expect(model.notes).toBe('Excellent rapport qualité/prix');
    expect(model.sortOrder).toBe(1);
  });

  it('gère les champs nullable', () => {
    const dtoNull: PackItemResponseDto = {
      ...dto,
      brand: null,
      model: null,
      replacement_km: null,
      notes: null,
      sort_order: null,
    };
    const model = mapPackItem(dtoNull);
    expect(model.brand).toBeNull();
    expect(model.model).toBeNull();
    expect(model.replacementKm).toBeNull();
    expect(model.notes).toBeNull();
    expect(model.sortOrder).toBeNull();
  });
});

// ─── Mapper PackScenario ──────────────────────────────────────────────────────

describe('mapPackScenario', () => {
  const dto: PackScenarioResponseDto = {
    id: 'sc-1',
    pilgrim_id: 'pilgrim-1',
    name: 'Été léger',
    description: 'Optimisé saison chaude',
    target_base_weight_kg: 8.0,
    configuration: 'solo',
    season: 'summer',
    season_label: 'Été',
    base_weight_g: 7500,
    base_weight_kg: 7.5,
    total_weight_g: 10250,
    total_weight_kg: 10.25,
    weight_indicator: 'ok',
    items: [],
    items_count: 0,
    created_at: '2026-01-01T00:00:00Z',
  };

  it('convertit le DTO en Model avec les champs calculés', () => {
    const model = mapPackScenario(dto);
    expect(model.id).toBe('sc-1');
    expect(model.name).toBe('Été léger');
    expect(model.targetBaseWeightKg).toBe(8.0);
    expect(model.baseWeightKg).toBe(7.5);
    expect(model.totalWeightKg).toBe(10.25);
    expect(model.weightIndicator).toBe('ok');
    expect(model.items).toHaveLength(0);
    expect(model.itemsCount).toBe(0);
  });

  it('mappe les items si présents', () => {
    const dtoWithItems: PackScenarioResponseDto = {
      ...dto,
      items: [
        {
          id: 'i-1',
          pack_scenario_id: 'sc-1',
          name: 'Tente',
          category: 'sleeping',
          category_label: 'Couchage',
          brand: null,
          model: null,
          weight_g: 1200,
          is_shared: true,
          is_consumable: false,
          replacement_km: null,
          notes: null,
          sort_order: 0,
          created_at: '2026-01-01T00:00:00Z',
        },
      ],
      items_count: 1,
    };
    const model = mapPackScenario(dtoWithItems);
    expect(model.items).toHaveLength(1);
    const firstItem = model.items[0];
    expect(firstItem?.name).toBe('Tente');
    expect(firstItem?.isShared).toBe(true);
    expect(model.itemsCount).toBe(1);
  });

  it('gère items undefined (liste sans eager loading)', () => {
    const dtoNoItems: PackScenarioResponseDto = { ...dto, items: undefined, items_count: 5 };
    const model = mapPackScenario(dtoNoItems);
    expect(model.items).toHaveLength(0);
    expect(model.itemsCount).toBe(5);
  });
});
