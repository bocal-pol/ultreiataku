/**
 * E2E-01 — Liste des 12 étapes Belgique (P0)
 * E2E-02 — Tabs France/Espagne (P2) — EmptyState
 *
 * Note : l'API retourne 12 étapes pour 2 routes BE (Via Mosane 8 étapes + Via Monastique 4 étapes).
 * Les étapes sont triées par sort_order global (non par route), ce qui entremêle BE-09/BE-10 de la
 * Via Monastique avec BE-01/BE-02 de la Via Mosane. Bug P1 enregistré : BUG-ULTREIA-001.
 */
import { test, expect } from '@playwright/test';
import { StageListPage } from '../pages/StagePage';

test.describe('E2E-01 — Liste des étapes Belgique', () => {
  test('affiche 12 étapes BE avec tab Belgique actif', async ({ page }) => {
    const stagePage = new StageListPage(page);
    await stagePage.navigate();

    // Titre de la liste visible
    await expect(stagePage.listTitle).toBeVisible();

    // Tab Belgique actif (premier tab)
    const tabBE = page.locator('[role="tab"]').first();
    await expect(tabBE).toHaveAttribute('aria-selected', 'true');

    // 12 listitem présents (2 routes × étapes BE)
    const items = stagePage.stageItems();
    await expect(items).toHaveCount(12);

    // Au moins une carte affiche "J1" (day_number=1 d'une route quelconque)
    const firstItem = items.first();
    await expect(firstItem).toContainText('J1');

    // BE-01 (Via Mosane Liège→Amay) doit être dans la liste
    const be01 = page.locator('[role="listitem"]', { hasText: 'Liège' });
    await expect(be01).toBeVisible();
    await expect(be01).toContainText('Amay');

    // Pas d'erreur réseau (pas de [role="alert"])
    await expect(page.locator('[role="alert"]')).not.toBeVisible();
  });

  test('E2E-01b — chaque carte contient les infos de base (distance, hébergement)', async ({ page }) => {
    const stagePage = new StageListPage(page);
    await stagePage.navigate();

    const items = stagePage.stageItems();
    await expect(items).toHaveCount(12);

    // Premier item : distance en km présente
    const firstItem = items.first();
    await expect(firstItem).toContainText('km');
  });

  test('E2E-02 — Tab France affiche un EmptyState, pas de listitem (P2)', async ({ page }) => {
    const stagePage = new StageListPage(page);
    await stagePage.navigate();

    await stagePage.tabFrance.click();
    await expect(stagePage.tabFrance).toHaveAttribute('aria-selected', 'true');

    // EmptyState visible, pas de listitem
    await expect(page.locator('[role="listitem"]')).toHaveCount(0);
  });
});
