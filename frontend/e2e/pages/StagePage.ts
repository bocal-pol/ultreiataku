/**
 * Page Object — Stages (liste + détail)
 * Sélecteurs basés sur data-testid, role et aria-label.
 */
import type { Page, Locator } from '@playwright/test';

export class StageListPage {
  readonly page: Page;
  readonly stageList: Locator;
  readonly tabBelgique: Locator;
  readonly tabFrance: Locator;
  readonly tabEspagne: Locator;
  readonly listTitle: Locator;

  constructor(page: Page) {
    this.page = page;
    this.stageList = page.locator('[role="list"]');
    this.tabBelgique = page.locator('[role="tab"]', { hasText: 'Belgique' });
    this.tabFrance = page.locator('[role="tab"]', { hasText: 'France' });
    this.tabEspagne = page.locator('[role="tab"]', { hasText: 'Espagne' });
    this.listTitle = page.locator('h1');
  }

  async navigate() {
    await this.page.goto('/belgique');
    await this.page.waitForLoadState('networkidle');
  }

  stageItems(): Locator {
    return this.page.locator('[role="listitem"]');
  }

  stageCardByDay(n: number): Locator {
    return this.page.locator(`[aria-label*="Jour ${n}"]`);
  }

  stageCardByText(text: string): Locator {
    return this.page.locator('[role="listitem"]', { hasText: text });
  }
}

export class StageDetailPage {
  readonly page: Page;
  readonly poiSection: Locator;
  readonly mealsSection: Locator;
  readonly nightSection: Locator;
  readonly miniMap: Locator;
  readonly gpxAuthRequired: Locator;
  readonly btnSeeOnMap: Locator;
  readonly accommodationPrimary: Locator;
  readonly accommodationAlternative: Locator;
  readonly bivouacZone: Locator;
  readonly mealLocalSpecialty: Locator;
  readonly backButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.poiSection = page.locator('[data-testid="poi-section"]');
    this.mealsSection = page.locator('[data-testid="meals-section"]');
    this.nightSection = page.locator('[data-testid="night-section"]');
    this.miniMap = page.locator('[data-testid="mini-map"]');
    this.gpxAuthRequired = page.locator('[data-testid="gpx-auth-required"]');
    this.btnSeeOnMap = page.locator('[data-testid="btn-see-on-map"]');
    this.accommodationPrimary = page.locator('[data-testid="accommodation-primary"]');
    this.accommodationAlternative = page.locator('[data-testid="accommodation-alternative"]');
    this.bivouacZone = page.locator('[data-testid="bivouac-zone"]');
    this.mealLocalSpecialty = page.locator('[data-testid="meal-local-specialty"]');
    this.backButton = page.locator('button[aria-label]').first();
  }

  async navigate(code: string) {
    await this.page.goto(`/etapes/${code}`);
    await this.page.waitForLoadState('networkidle');
  }

  mealRow(type: string): Locator {
    return this.page.locator(`[data-testid="meal-row-${type}"]`);
  }

  header(): Locator {
    return this.page.locator('header').first();
  }
}

export class MapPage {
  readonly page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  async navigate(code?: string) {
    const path = code ? `/carte/${code}` : '/carte';
    await this.page.goto(path);
    await this.page.waitForLoadState('networkidle');
  }

  leafletContainer(): Locator {
    return this.page.locator('.leaflet-container');
  }

  offlineStatus(): Locator {
    return this.page.locator('[role="status"]');
  }
}
