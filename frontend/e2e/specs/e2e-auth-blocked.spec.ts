/**
 * E2E-17/19/20/25/31/32/33/36 — Scénarios AUTH bloqués
 *
 * L'AuthGuard appelle redirectToLogin() (navigation externe vers SSO),
 * retourne null si non authentifié → page rendue vide (fond noir).
 * Playwright ne suit pas la navigation externe (SSO hors contexte).
 * Le test vérifie que la page reste sur 5181 (SPA sans rendu visible)
 * ou que le body est vide/ne contient pas le composant protégé.
 *
 * BLOQUÉ pour les flux complets : app Auth (8082) non initialisée avec
 * des comptes de test. Ces specs sont prêtes pour la CI une fois Auth
 * configuré avec un utilisateur de test headless.
 */
import { test, expect } from '@playwright/test';

test.describe('Routes protégées — AuthGuard (BLOQUÉ sans session Auth)', () => {
  test('E2E-25 — /sac : AuthGuard rend la page vide (redirection SSO déclenchée)', async ({ page }) => {
    await page.goto('/sac');
    await page.waitForTimeout(3_000);

    // Deux cas acceptables :
    // 1. Page rend null (body sans contenu applicatif) = comportement AuthGuard correct
    // 2. Navigation externe vers SSO (URL différente de 5181/sac)
    const url = page.url();
    const isStillOnSac = url.includes('/sac') && url.includes('5181');
    const bodyText = await page.locator('body').innerText().catch(() => '');

    // Si on est encore sur /sac, le body doit être vide (AuthGuard retourne null)
    if (isStillOnSac) {
      // La page doit être vide ou contenir uniquement la BottomNav
      const hasProtectedContent = bodyText.includes('Scénario') || bodyText.includes('pack');
      expect(hasProtectedContent).toBe(false);
    } else {
      // Redirection SSO réussie
      expect(url).not.toContain('/sac');
    }
  });

  test('E2E-19 — /trips/new : AuthGuard rend la page vide (redirection SSO déclenchée)', async ({ page }) => {
    await page.goto('/trips/new');
    await page.waitForTimeout(3_000);

    const url = page.url();
    const bodyText = await page.locator('body').innerText().catch(() => '');
    const isStillOnTrips = url.includes('/trips/new') && url.includes('5181');

    if (isStillOnTrips) {
      const hasProtectedContent = bodyText.includes('Créer') && bodyText.includes('voyage');
      expect(hasProtectedContent).toBe(false);
    } else {
      expect(url).not.toContain('/trips/new');
    }
  });

  test('E2E-31 — /journal/{tripId} : AuthGuard rend la page vide (BLOQUÉ — nécessite session Auth)', async ({ page }) => {
    test.skip(true, 'BLOQUÉ — E2E-17/31/32/33/36 nécessitent une session Auth SSO active. App Auth (8082) UP mais aucun compte de test headless configuré.');
  });

  test('E2E-17 — Login SSO complet (BLOQUÉ — nécessite compte de test Auth)', async ({ page }) => {
    test.skip(true, 'BLOQUÉ — E2E-17 : flux login complet SSO nécessite utilisateur de test dans Auth DB (8082). Prêt à activer une fois seed Auth test créé.');
  });
});
