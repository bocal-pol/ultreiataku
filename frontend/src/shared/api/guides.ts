/**
 * Service API — Guides du Chemin
 *
 * Lecture publique — pas d'authentification requise.
 * Le backend expose :
 *   GET  /api/pilgrimage/guides         → liste groupée par catégorie
 *   GET  /api/pilgrimage/guides/{slug}  → contenu markdown d'un guide
 *
 * Pattern : DTO reçu → mapper → Model UI retourné.
 * Aucun DTO ne franchit cette fonction vers les composants.
 */

import { apiFetch } from './client.ts';
import type {
  GuideListResponseDto,
  GuideDetailResponseDto,
} from '../../dtos/pilgrimage.ts';
import { mapGuideListItem, mapGuideDetail } from '../../mappers/pilgrimage.ts';
import type { GuideListItemModel, GuideDetailModel } from '../../models/pilgrimage.ts';

/**
 * Récupère la liste de tous les guides publiés.
 * Le backend renvoie `{ data: { "Le Corps": GuideListItemResponseDto[], "Pratique": [...] } }`
 * (objet groupé par catégorie). On l'aplatit en tableau — le composant regroupe
 * lui-même par `category` en préservant l'ordre.
 */
export async function fetchGuides(signal?: AbortSignal): Promise<GuideListItemModel[]> {
  const dto = await apiFetch<GuideListResponseDto>('/guides', { signal });
  const groups = dto.data ?? {};
  return Object.values(groups)
    .flat()
    .map(mapGuideListItem);
}

/**
 * Récupère le détail d'un guide (contenu markdown) par son slug.
 */
export async function fetchGuideDetail(
  slug: string,
  signal?: AbortSignal,
): Promise<GuideDetailModel> {
  // L'API enveloppe la ressource dans `{ data: {...} }` — déballer avant mapping.
  const res = await apiFetch<{ data: GuideDetailResponseDto }>(`/guides/${slug}`, { signal });
  return mapGuideDetail(res.data);
}
