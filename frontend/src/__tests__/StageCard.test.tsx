import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import { StageCard } from '../features/pilgrimage/stages/StageCard.tsx';
import type { StageModel } from '../models/pilgrimage.ts';
import '../shared/i18n/i18n.ts';

const mockNavigate = vi.fn();
vi.mock('react-router-dom', async (importOriginal) => {
  const actual = await importOriginal<typeof import('react-router-dom')>();
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  };
});

const stage: StageModel = {
  id: 'stage-1',
  code: 'BE-01',
  name: 'Liège — Amay',
  dayNumber: 1,
  distanceKm: 22,
  elevationGainM: 250,
  elevationLossM: 200,
  estimatedDurationH: 5.5,
  difficulty: 'easy',
  accommodationTypeDefault: 'gite',
  notes: null,
  startWaypoint: {
    id: 'wp-1', slug: 'liege', name: 'Liège', type: 'city',
    poiCategory: null, lat: 50.64, lng: 5.57, detourType: null,
    detourDistanceKm: null, detourDurationMin: null, visitDurationMin: null,
    entryCostEur: null, bookingRequired: false, bookingContact: null,
    openingNotes: null, description: null, isActive: true,
  },
  endWaypoint: {
    id: 'wp-2', slug: 'amay', name: 'Amay', type: 'city',
    poiCategory: null, lat: 50.55, lng: 5.32, detourType: null,
    detourDistanceKm: null, detourDurationMin: null, visitDurationMin: null,
    entryCostEur: null, bookingRequired: false, bookingContact: null,
    openingNotes: null, description: null, isActive: true,
  },
};

function renderCard() {
  return render(
    <MemoryRouter>
      <StageCard stage={stage} />
    </MemoryRouter>,
  );
}

describe('StageCard', () => {
  it('affiche départ et arrivée', () => {
    renderCard();
    expect(screen.getByText(/Liège/)).toBeInTheDocument();
    expect(screen.getByText(/Amay/)).toBeInTheDocument();
  });

  it('affiche la distance', () => {
    renderCard();
    expect(screen.getByText(/22 km/)).toBeInTheDocument();
  });

  it('navigue vers le détail au clic', async () => {
    const user = userEvent.setup();
    renderCard();
    const card = screen.getByRole('listitem');
    await user.click(card);
    expect(mockNavigate).toHaveBeenCalledWith('/etapes/BE-01');
  });

  it('navigue au Enter', async () => {
    const user = userEvent.setup();
    renderCard();
    const card = screen.getByRole('listitem');
    card.focus();
    await user.keyboard('{Enter}');
    expect(mockNavigate).toHaveBeenCalledWith('/etapes/BE-01');
  });

  it('a un aria-label accessible', () => {
    renderCard();
    const card = screen.getByRole('listitem');
    expect(card).toHaveAttribute('aria-label');
    expect(card.getAttribute('aria-label')).toContain('Liège');
  });

  it('est focusable (tabIndex=0)', () => {
    renderCard();
    const card = screen.getByRole('listitem');
    expect(card).toHaveAttribute('tabindex', '0');
  });
});
