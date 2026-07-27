/**
 * Tests — WhoCarriesWhat
 * Couvre :
 * - Rendu de base avec étapes et membres
 * - Sélection d'une étape → affiche membres + bandeau dégradation
 * - Aucun membre → message vide
 * - Dégradation : bandeau informatif visible quand une étape est sélectionnée
 */

import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { WhoCarriesWhat } from '../features/pilgrimage/pack/WhoCarriesWhat.tsx';
import type { TripModel } from '../models/pilgrimage.ts';

// ── Mocks ─────────────────────────────────────────────────────────────────────

vi.mock('../shared/i18n/i18n.ts', () => ({}));

vi.mock('react-i18next', () => ({
  useTranslation: () => ({
    t: (key: string, opts?: Record<string, unknown>) => {
      if (opts) return Object.entries(opts).reduce((s, [k, v]) => s.replace(`{{${k}}}`, String(v)), key);
      return key;
    },
    i18n: { language: 'fr' },
  }),
}));

// ── Fixtures ─────────────────────────────────────────────────────────────────

const makePilgrim = (id: string, name: string) => ({
  id,
  userId: 1,
  displayName: name,
  avatarUrl: null,
  preferredLocale: 'fr' as const,
  configuration: 'solo' as const,
});

const makeStage = (id: string, dayNumber: number, name: string) => ({
  id,
  code: `LGE-0${dayNumber}`,
  name,
  dayNumber,
  distanceKm: 25,
  elevationGainM: 100,
  elevationLossM: 100,
  estimatedDurationH: 7,
  difficulty: 'moderate' as const,
  accommodationTypeDefault: 'gite',
  notes: null,
  startWaypoint: { id: 'wp1', slug: 'liege', name: 'Liège', type: 'city' as const, poiCategory: null, lat: 50.64, lng: 5.57, detourType: null, detourDistanceKm: null, detourDurationMin: null, visitDurationMin: null, entryCostEur: null, bookingRequired: false, bookingContact: null, openingNotes: null, description: null, isActive: true },
  endWaypoint: { id: 'wp2', slug: 'huy', name: 'Huy', type: 'city' as const, poiCategory: null, lat: 50.52, lng: 5.24, detourType: null, detourDistanceKm: null, detourDurationMin: null, visitDurationMin: null, entryCostEur: null, bookingRequired: false, bookingContact: null, openingNotes: null, description: null, isActive: true },
});

const mockTrip: TripModel = {
  id: 'trip-1',
  name: 'Test Trip',
  description: null,
  status: 'planned',
  estimatedStartDate: '2027-05-01',
  estimatedEndDate: null,
  configuration: 'duo',
  isPublic: false,
  inviteToken: null,
  route: {
    id: 'route-1',
    slug: 'via-mosana',
    name: 'Via Mosana',
    country: 'BE',
    totalDistanceKm: 2260,
    totalElevationGainM: 12000,
    stages: [
      makeStage('s1', 1, 'Liège → Huy'),
      makeStage('s2', 2, 'Huy → Namur'),
    ],
  },
  members: [
    { pilgrim: makePilgrim('p1', 'Pascal'), role: 'organizer', joinedAt: '2027-01-01' },
    { pilgrim: makePilgrim('p2', 'Marie'), role: 'participant', joinedAt: '2027-01-02' },
    { pilgrim: makePilgrim('p3', 'Paul'), role: 'observer', joinedAt: '2027-01-03' }, // filtré
  ],
  organizer: makePilgrim('p1', 'Pascal'),
  createdAt: '2027-01-01',
};

const tripNoMembers: TripModel = {
  ...mockTrip,
  members: [],
};

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('WhoCarriesWhat', () => {
  it('se rend avec le sélecteur d\'étape', () => {
    render(<WhoCarriesWhat trip={mockTrip} />);
    expect(screen.getByTestId('who-carries-what')).toBeInTheDocument();
    // Select présent
    const select = screen.getByRole('combobox');
    expect(select).toBeInTheDocument();
    // Options étapes
    expect(screen.getByText('J1 — Liège → Huy')).toBeInTheDocument();
    expect(screen.getByText('J2 — Huy → Namur')).toBeInTheDocument();
  });

  it('n\'affiche pas la liste des membres avant sélection d\'étape', () => {
    render(<WhoCarriesWhat trip={mockTrip} />);
    expect(screen.queryByRole('list')).not.toBeInTheDocument();
    expect(screen.queryByTestId('assignments-degraded-notice')).not.toBeInTheDocument();
  });

  it('affiche les membres (sans observer) après sélection d\'une étape', async () => {
    const user = userEvent.setup();
    render(<WhoCarriesWhat trip={mockTrip} />);
    await user.selectOptions(screen.getByRole('combobox'), 's1');
    expect(screen.getByRole('list')).toBeInTheDocument();
    expect(screen.getByText('Pascal')).toBeInTheDocument();
    expect(screen.getByText('Marie')).toBeInTheDocument();
    // L'observer est filtré
    expect(screen.queryByText('Paul')).not.toBeInTheDocument();
  });

  it('affiche le bandeau de dégradation après sélection d\'étape', async () => {
    const user = userEvent.setup();
    render(<WhoCarriesWhat trip={mockTrip} />);
    await user.selectOptions(screen.getByRole('combobox'), 's1');
    expect(screen.getByTestId('assignments-degraded-notice')).toBeInTheDocument();
  });

  it('affiche un message vide si aucun membre non-observer', () => {
    render(<WhoCarriesWhat trip={tripNoMembers} />);
    // pas de select étape ou message vide
    // Le composant montre le message 'trip.members_section'
    expect(screen.getByText(/trip\.members_section/)).toBeInTheDocument();
  });
});
