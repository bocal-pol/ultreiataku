import { describe, it, expect } from 'vitest';
import frPilgrimage from '../shared/i18n/fr/pilgrimage.json';
import nlPilgrimage from '../shared/i18n/nl/pilgrimage.json';
import dePilgrimage from '../shared/i18n/de/pilgrimage.json';

/** Retourne toutes les feuilles d'un objet JSON imbriqué comme tableau de chemins "a.b.c" */
function getAllLeafPaths(obj: unknown, prefix = ''): string[] {
  if (typeof obj !== 'object' || obj === null) return [prefix];
  return Object.entries(obj as Record<string, unknown>).flatMap(([key, val]) =>
    getAllLeafPaths(val, prefix ? `${prefix}.${key}` : key),
  );
}

describe('Couverture i18n — clés identiques fr/nl/de', () => {
  const frKeys = new Set(getAllLeafPaths(frPilgrimage));
  const nlKeys = new Set(getAllLeafPaths(nlPilgrimage));
  const deKeys = new Set(getAllLeafPaths(dePilgrimage));

  it('nl contient toutes les clés de fr', () => {
    const missing = [...frKeys].filter(k => !nlKeys.has(k));
    expect(missing).toHaveLength(0);
  });

  it('de contient toutes les clés de fr', () => {
    const missing = [...frKeys].filter(k => !deKeys.has(k));
    expect(missing).toHaveLength(0);
  });

  it('fr contient toutes les clés de nl', () => {
    const missing = [...nlKeys].filter(k => !frKeys.has(k));
    expect(missing).toHaveLength(0);
  });

  it('fr contient toutes les clés de de', () => {
    const missing = [...deKeys].filter(k => !frKeys.has(k));
    expect(missing).toHaveLength(0);
  });

  it('nombre de clés fr suffisant (>= 80)', () => {
    expect(frKeys.size).toBeGreaterThanOrEqual(80);
  });
});
