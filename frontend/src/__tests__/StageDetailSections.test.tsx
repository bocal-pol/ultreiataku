/**
 * Tests — Sections Hébergement (ULTREIA-24) et Repas (ULTREIA-25)
 * Vitest + React Testing Library
 */
import React from 'react';
import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { StageDetailScreen } from '../features/pilgrimage/stages/StageDetailScreen.tsx';
import type { StageDetailModel, AccommodationModel, MealModel } from '../models/pilgrimage.ts';
import '../shared/i18n/i18n.ts';

// ── Mocks navigation ──────────────────────────────────────────────────────────

const mockNavigate = vi.fn();
vi.mock('react-router-dom', async (importOriginal) => {
  const actual = await importOriginal<typeof import('react-router-dom')>();
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  };
});

// ── Mock hooks ────────────────────────────────────────────────────────────────

vi.mock('../shared/hooks/useStages.ts', () => ({
  useStageDetail: vi.fn(),
  useStages: vi.fn(),
}));

vi.mock('../shared/hooks/useGpx.ts', () => ({
  useGpxSimplified: vi.fn(() => ({ data: null, isError: false })),
}));
// Mock AuthContext (Provider/Guard uniquement)
vi.mock('../context/AuthContext.tsx', () => ({
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthGuard: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

// Mock useAuth (hook séparé depuis Phase C)
vi.mock('../context/useAuth.ts', () => ({
  useAuth: vi.fn().mockReturnValue({
    currentUser: null,
    isAuthenticated: false,
    isLoading: false,
    login: vi.fn(),
    logout: vi.fn(),
  }),
}));

// Mock useTrips pour AccommodationOccupancyBadge (tripId=null -> disabled)
vi.mock('../shared/hooks/useTrips.ts', () => ({
  useOccupancy: vi.fn(() => ({ data: undefined, isLoading: false, isError: false })),
  tripKeys: { occupancy: (id: string) => ['trips', 'occupancy', id] as const },
}));

// MiniMap lazy-load
vi.mock('../features/pilgrimage/map/MiniMap.tsx', () => ({
  default: () => <div data-testid="mini-map-stub" />,
}));

import { useStageDetail } from '../shared/hooks/useStages.ts';

// ── Fixtures ──────────────────────────────────────────────────────────────────

const baseWaypoint = {
  id: 'wp-1', slug: 'andenne', name: 'Andenne', type: 'city' as const,
  poiCategory: null, lat: 50.48, lng: 5.10, detourType: null,
  detourDistanceKm: null, detourDurationMin: null, visitDurationMin: null,
  entryCostEur: null, bookingRequired: false, bookingContact: null,
  openingNotes: null, description: null, isActive: true,
};

const primaryAccom: AccommodationModel = {
  id: 'accom-1',
  name: 'Gîte Andenne Principal',
  type: 'gite',
  address: '12 Rue du Pèlerin, Andenne',
  phone: '+32498000001',
  website: null,
  email: 'gite@andenne.be',
  priceMinEur: 15,
  priceMaxEur: 20,
  isDonativo: false,
  capacity: 8,
  hasShower: true,
  hasKitchen: true,
  hasWifi: false,
  stampsCredencial: true,
  bookingRequired: true,
  bookingNoticeDays: 2,
  bivouacLegal: false,
  bivouacNotes: null,
  isPrimary: true,
  notes: null,
  verifiedAt: null,
  isObsolete: false,
};

const altAccom: AccommodationModel = {
  id: 'accom-2',
  name: 'Camping des Trois Bras',
  type: 'camping',
  address: null,
  phone: '+32498000002',
  website: null,
  email: null,
  priceMinEur: 8,
  priceMaxEur: null,
  isDonativo: false,
  capacity: 20,
  hasShower: true,
  hasKitchen: false,
  hasWifi: false,
  stampsCredencial: false,
  bookingRequired: false,
  bookingNoticeDays: null,
  bivouacLegal: false,
  bivouacNotes: null,
  isPrimary: false,
  notes: null,
  verifiedAt: null,
  isObsolete: false,
};

const bivouacAccom: AccommodationModel = {
  id: 'accom-3',
  name: 'Zone bivouac Vieux Moulin',
  type: 'bivouac',
  address: null,
  phone: null,
  website: null,
  email: null,
  priceMinEur: 0,
  priceMaxEur: null,
  isDonativo: false,
  capacity: null,
  hasShower: false,
  hasKitchen: false,
  hasWifi: false,
  stampsCredencial: false,
  bookingRequired: false,
  bookingNoticeDays: null,
  bivouacLegal: true,
  bivouacNotes: 'Zone boisée à 500 m de la route.',
  isPrimary: false,
  notes: null,
  verifiedAt: null,
  isObsolete: false,
};

// verifiedAt ancien (> 6 mois) pour tester le badge
const oldVerifiedAccom: AccommodationModel = {
  ...primaryAccom,
  id: 'accom-old',
  verifiedAt: new Date(Date.now() - 1000 * 60 * 60 * 24 * 250).toISOString(), // ~8 mois
};

const breakfastMeal: MealModel = {
  id: 'meal-1',
  mealType: 'breakfast',
  name: 'Pain et confiture',
  description: null,
  mealContext: 'grocery',
  restaurantName: null,
  restaurantAddress: null,
  priceEstimateEur: 3,
  kcalEstimate: 400,
  weightG: null,
  notes: null,
};

const lunchSpecialty: MealModel = {
  id: 'meal-2',
  mealType: 'lunch',
  name: 'Pistolet andennais',
  description: 'Sandwich local typique garni de jambon et fromage.',
  mealContext: 'local_specialty',
  restaurantName: 'Boulangerie du Centre',
  restaurantAddress: 'Rue de Namur 3, Andenne',
  priceEstimateEur: 5,
  kcalEstimate: 650,
  weightG: null,
  notes: null,
};

const dinnerMeal: MealModel = {
  id: 'meal-3',
  mealType: 'dinner',
  name: 'Diner du soir',
  description: null,
  mealContext: 'restaurant',
  restaurantName: 'Table du Randonneur',
  restaurantAddress: null,
  priceEstimateEur: 12,
  kcalEstimate: 700,
  weightG: null,
  notes: null,
};

function makeStage(overrides: Partial<StageDetailModel> = {}): StageDetailModel {
  return {
    id: 'stage-3',
    code: 'BE-03',
    name: 'Huy — Andenne',
    dayNumber: 3,
    distanceKm: 18,
    elevationGainM: 200,
    elevationLossM: 180,
    estimatedDurationH: 4.5,
    difficulty: 'moderate',
    accommodationTypeDefault: 'gite',
    notes: null,
    startWaypoint: { ...baseWaypoint, id: 'wp-huy', slug: 'huy', name: 'Huy' },
    endWaypoint: baseWaypoint,
    waypoints: [],
    accommodations: [primaryAccom, altAccom],
    meals: [breakfastMeal, lunchSpecialty, dinnerMeal],
    gpxTraces: [],
    ...overrides,
  };
}

// ── Helpers de rendu ──────────────────────────────────────────────────────────

function renderDetail(stage: StageDetailModel | null, { loading = false, error = false } = {}) {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  vi.mocked(useStageDetail).mockReturnValue({
    data: stage ?? undefined,
    isLoading: loading,
    isError: error,
  } as ReturnType<typeof useStageDetail>);

  return render(
    <QueryClientProvider client={qc}>
      <MemoryRouter initialEntries={['/etapes/BE-03']}>
        <Routes>
          <Route path="/etapes/:code" element={<StageDetailScreen />} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

// ── Tests hébergement (ULTREIA-24) ────────────────────────────────────────────

describe('ULTREIA-24 — Section Hébergement', () => {
  it('affiche la section nuit avec data-testid', () => {
    renderDetail(makeStage());
    expect(screen.getByTestId('night-section')).toBeInTheDocument();
  });

  it('affiche le nom de l\'hébergement principal', () => {
    renderDetail(makeStage());
    // getAllByText car le type "Gîte" peut apparaître ailleurs ; on cible le nom unique
    expect(screen.getAllByText('Gîte Andenne Principal').length).toBeGreaterThanOrEqual(1);
  });

  it('affiche l\'adresse de l\'hébergement principal', () => {
    renderDetail(makeStage());
    expect(screen.getByText('12 Rue du Pèlerin, Andenne')).toBeInTheDocument();
  });

  it('affiche le tarif min–max', () => {
    renderDetail(makeStage());
    expect(screen.getByText('15–20 €')).toBeInTheDocument();
  });

  it('affiche les équipements Douche et Cuisine', () => {
    renderDetail(makeStage());
    const showers = screen.getAllByText(/Douche/);
    expect(showers.length).toBeGreaterThanOrEqual(1);
  });

  it('affiche le lien téléphone cliquable', () => {
    renderDetail(makeStage());
    const telLink = screen.getByRole('link', { name: /\+32498000001/ });
    expect(telLink).toHaveAttribute('href', 'tel:+32498000001');
  });

  it('affiche le lien email cliquable', () => {
    renderDetail(makeStage());
    const mailLink = screen.getByRole('link', { name: /gite@andenne\.be/ });
    expect(mailLink).toHaveAttribute('href', 'mailto:gite@andenne.be');
  });

  it('affiche un badge de réservation', () => {
    renderDetail(makeStage());
    // Le badge contient "Réserver" ou "Réservation"
    const badge = screen.getByText(/[Rr]éserv/);
    expect(badge).toBeInTheDocument();
  });

  it('affiche la capacité de l\'hébergement', () => {
    renderDetail(makeStage());
    // "Capacité : 8 pèlerins" ou similaire
    expect(screen.getByText(/apacit/)).toBeInTheDocument();
  });

  it('les alternatives sont dans un accordéon fermé par défaut', () => {
    renderDetail(makeStage());
    // L'accordéon est fermé : le texte unique à l'alternative n'est pas visible
    expect(screen.queryByText('Camping des Trois Bras')).not.toBeInTheDocument();
  });

  it('les alternatives s\'ouvrent au clic sur l\'accordéon', async () => {
    const user = userEvent.setup();
    renderDetail(makeStage());
    const toggleBtn = screen.getByRole('button', { name: /[Aa]lternative/i });
    await user.click(toggleBtn);
    expect(screen.getByText('Camping des Trois Bras')).toBeInTheDocument();
  });

  it('affiche la zone bivouac légal avec data-testid', () => {
    renderDetail(makeStage({
      accommodations: [primaryAccom, bivouacAccom],
    }));
    expect(screen.getByTestId('bivouac-zone')).toBeInTheDocument();
    expect(screen.getByText(/Zone boisée/)).toBeInTheDocument();
  });

  it('n\'affiche pas le badge "vérifié" si verifiedAt < 6 mois', () => {
    const recentVerifiedAccom: AccommodationModel = {
      ...primaryAccom,
      verifiedAt: new Date(Date.now() - 1000 * 60 * 60 * 24 * 30).toISOString(), // 1 mois
    };
    renderDetail(makeStage({ accommodations: [recentVerifiedAccom] }));
    expect(screen.queryByText(/[Vv]érifié il y a/)).not.toBeInTheDocument();
  });

  it('affiche le badge "vérifié il y a X mois" si verifiedAt > 6 mois', () => {
    renderDetail(makeStage({ accommodations: [oldVerifiedAccom] }));
    expect(screen.getByText(/[Vv]érifié il y a \d+ mois/)).toBeInTheDocument();
  });

  it('hébergement sans hébergements : section absente', () => {
    renderDetail(makeStage({ accommodations: [] }));
    expect(screen.queryByTestId('night-section')).not.toBeInTheDocument();
  });
});

// ── Tests repas (ULTREIA-25) ──────────────────────────────────────────────────

describe('ULTREIA-25 — Section Repas', () => {
  it('affiche la section repas avec data-testid (section HTML)', () => {
    renderDetail(makeStage());
    // La section <section data-testid="meals-section"> est unique
    const sections = screen.getAllByTestId('meals-section');
    // Il peut y avoir la section + le div interne : on vérifie qu'au moins une est une section
    const sectionEl = sections.find(el => el.tagName.toLowerCase() === 'section');
    expect(sectionEl).toBeDefined();
  });

  it('affiche la spécialité locale en tête avec data-testid', () => {
    renderDetail(makeStage());
    expect(screen.getByTestId('meal-local-specialty')).toBeInTheDocument();
    expect(screen.getByText('Pistolet andennais')).toBeInTheDocument();
  });

  it('affiche la description de la spécialité locale', () => {
    renderDetail(makeStage());
    expect(screen.getByText(/Sandwich local typique/)).toBeInTheDocument();
  });

  it('affiche le repas petit-déjeuner avec data-testid', () => {
    renderDetail(makeStage());
    expect(screen.getByTestId('meal-row-breakfast')).toBeInTheDocument();
  });

  it('affiche le repas dîner avec data-testid', () => {
    renderDetail(makeStage());
    expect(screen.getByTestId('meal-row-dinner')).toBeInTheDocument();
  });

  it('affiche la ration journalière totale', () => {
    renderDetail(makeStage());
    // 400 + 650 + 700 = 1750 kcal
    const ration = screen.getByTestId('meal-daily-ration');
    expect(ration).toBeInTheDocument();
    expect(ration.textContent).toContain('1750');
  });

  it('n\'affiche pas la ration si aucun repas n\'a de kcalEstimate', () => {
    const mealsNoKcal: MealModel[] = [
      { ...breakfastMeal, kcalEstimate: null },
      { ...dinnerMeal, kcalEstimate: null },
    ];
    renderDetail(makeStage({ meals: mealsNoKcal }));
    expect(screen.queryByTestId('meal-daily-ration')).not.toBeInTheDocument();
  });

  it('section repas absente si aucun repas', () => {
    renderDetail(makeStage({ meals: [] }));
    expect(screen.queryByTestId('meals-section')).not.toBeInTheDocument();
  });

  it('affiche l\'adresse du restaurant pour la spécialité', () => {
    renderDetail(makeStage());
    expect(screen.getByText('Rue de Namur 3, Andenne')).toBeInTheDocument();
  });

  it('affiche le nom du restaurant du dîner', () => {
    renderDetail(makeStage());
    expect(screen.getByText('Table du Randonneur')).toBeInTheDocument();
  });
});

// ── Tests data-testid mini-map / btn-see-on-map ───────────────────────────────

describe('data-testid mini-map / btn-see-on-map', () => {
  it('data-testid="btn-see-on-map" absent si pas de gpxLine', () => {
    renderDetail(makeStage());
    // gpxLine est null (mock), donc la div mini-map n'est pas rendue
    expect(screen.queryByTestId('btn-see-on-map')).not.toBeInTheDocument();
  });
});

// ── Tests mapper AccommodationResponseDto → AccommodationModel ────────────────

import { mapAccommodation, mapMeal } from '../mappers/pilgrimage.ts';
import type { AccommodationResponseDto, MealResponseDto } from '../dtos/pilgrimage.ts';

describe('mapAccommodation — nouveaux champs vague 1b', () => {
  const dto: AccommodationResponseDto = {
    id: 'a-1',
    name: 'Test Gîte',
    type: 'gite',
    address: 'Rue du Test 1',
    phone: '+32477000000',
    website: 'https://exemple.be',
    email: 'test@exemple.be',
    price_min_eur: 15,
    price_max_eur: 20,
    is_donativo: false,
    capacity: 10,
    has_shower: true,
    has_kitchen: false,
    has_wifi: true,
    stamps_credencial: true,
    pilgrim_friendly: true,
    booking_required: true,
    booking_notice_days: 3,
    bivouac_legal: false,
    bivouac_notes: null,
    is_primary: true,
    sort_order: 1,
    notes: 'Vue sur la Meuse.',
    verified_at: '2026-01-15T00:00:00Z',
    is_obsolete: false,
  };

  it('mappe address', () => {
    expect(mapAccommodation(dto).address).toBe('Rue du Test 1');
  });

  it('mappe bookingNoticeDays', () => {
    expect(mapAccommodation(dto).bookingNoticeDays).toBe(3);
  });

  it('mappe verifiedAt', () => {
    expect(mapAccommodation(dto).verifiedAt).toBe('2026-01-15T00:00:00Z');
  });

  it('mappe isObsolete false', () => {
    expect(mapAccommodation(dto).isObsolete).toBe(false);
  });

  it('mappe is_obsolete = true', () => {
    expect(mapAccommodation({ ...dto, is_obsolete: true }).isObsolete).toBe(true);
  });

  it('mappe bivouac_legal et bivouac_notes', () => {
    const m = mapAccommodation({ ...dto, bivouac_legal: true, bivouac_notes: 'Zone nord.' });
    expect(m.bivouacLegal).toBe(true);
    expect(m.bivouacNotes).toBe('Zone nord.');
  });
});

describe('mapMeal — nouveaux champs vague 1b', () => {
  const dto: MealResponseDto = {
    id: 'm-1',
    meal_type: 'lunch',
    name: 'Pistolet',
    description: 'Sandwich local.',
    meal_context: 'local_specialty',
    restaurant_name: 'Boulangerie du Centre',
    restaurant_address: 'Rue Principale 1',
    price_estimate_eur: 5,
    kcal_estimate: 650,
    weight_g: 200,
    notes: null,
  };

  it('mappe restaurantAddress', () => {
    expect(mapMeal(dto).restaurantAddress).toBe('Rue Principale 1');
  });

  it('mappe kcalEstimate', () => {
    expect(mapMeal(dto).kcalEstimate).toBe(650);
  });

  it('mappe weightG', () => {
    expect(mapMeal(dto).weightG).toBe(200);
  });

  it('mappe mealContext local_specialty', () => {
    expect(mapMeal(dto).mealContext).toBe('local_specialty');
  });

  it('gère kcal_estimate null', () => {
    expect(mapMeal({ ...dto, kcal_estimate: null }).kcalEstimate).toBeNull();
  });
});
