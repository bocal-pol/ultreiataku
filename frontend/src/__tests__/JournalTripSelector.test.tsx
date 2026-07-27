/**
 * Tests — JournalTripSelectorScreen
 * Couvre :
 * - Affiche skeleton pendant le chargement
 * - Redirige directement si un seul voyage
 * - Affiche la liste si plusieurs voyages
 * - Affiche message + CTA créer si aucun voyage
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { JournalTripSelectorScreen } from '../features/pilgrimage/journal/JournalTripSelectorScreen.tsx';

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

vi.mock('../shared/api/auth.ts', () => ({
  fetchMe: vi.fn(),
  redirectToLogin: vi.fn(),
  logout: vi.fn(),
}));

vi.mock('../shared/hooks/useTrips.ts', async () => {
  const actual = await vi.importActual('../shared/hooks/useTrips.ts');
  return {
    ...actual,
    useMyTrips: vi.fn(),
  };
});

import { useMyTrips } from '../shared/hooks/useTrips.ts';

vi.mock('../context/AuthContext.tsx', () => ({
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthGuard: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthContext: { _currentValue: null },
}));

vi.mock('../context/useAuth.ts', () => ({
  useAuth: () => ({
    currentUser: {
      userId: 1,
      name: 'Pascal',
      email: 'pascal@test.be',
      pilgrim: { id: 'p1', userId: 1, displayName: 'Pascal', avatarUrl: null, preferredLocale: 'fr', configuration: 'solo' },
    },
    isAuthenticated: true,
    isLoading: false,
    login: vi.fn(),
    logout: vi.fn(),
  }),
}));

// ── Fixtures ─────────────────────────────────────────────────────────────────

const makeTrip = (id: string, name: string) => ({
  id,
  name,
  description: null,
  status: 'planned' as const,
  estimatedStartDate: '2027-05-01',
  estimatedEndDate: null,
  configuration: 'solo' as const,
  isPublic: false,
  inviteToken: null,
  route: {
    id: 'route-1',
    slug: 'via-mosana',
    name: 'Via Mosana',
    country: 'BE' as const,
    totalDistanceKm: 2260,
    totalElevationGainM: 12000,
    stages: [],
  },
  members: [],
  organizer: { id: 'p1', userId: 1, displayName: 'Pascal', avatarUrl: null, preferredLocale: 'fr' as const, configuration: 'solo' as const },
  createdAt: '2027-01-01',
});

function makeQueryClient() {
  return new QueryClient({ defaultOptions: { queries: { retry: false } } });
}

function Wrapper() {
  return (
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={['/journal']}>
        <Routes>
          <Route path="/journal" element={<JournalTripSelectorScreen />} />
          <Route path="/journal/:tripId" element={<div data-testid="journal-screen">Journal</div>} />
          <Route path="/trips/new" element={<div data-testid="create-trip">Créer</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>
  );
}

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('JournalTripSelectorScreen', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('affiche un skeleton pendant le chargement', () => {
    (useMyTrips as ReturnType<typeof vi.fn>).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
    });
    render(<Wrapper />);
    expect(screen.getByTestId('journal-trip-selector')).toBeInTheDocument();
    // skeleton — pas de liste
    expect(screen.queryByTestId('trip-selector-list')).not.toBeInTheDocument();
  });

  it('redirige directement vers /journal/:tripId si un seul voyage', async () => {
    (useMyTrips as ReturnType<typeof vi.fn>).mockReturnValue({
      data: [makeTrip('trip-solo', 'Mon voyage')],
      isLoading: false,
      isError: false,
    });
    render(<Wrapper />);
    await waitFor(() => {
      expect(screen.getByTestId('journal-screen')).toBeInTheDocument();
    });
  });

  it('affiche la liste des voyages si plusieurs', () => {
    (useMyTrips as ReturnType<typeof vi.fn>).mockReturnValue({
      data: [makeTrip('trip-1', 'Voyage Liège'), makeTrip('trip-2', 'Voyage Namur')],
      isLoading: false,
      isError: false,
    });
    render(<Wrapper />);
    expect(screen.getByTestId('trip-selector-list')).toBeInTheDocument();
    expect(screen.getByTestId('trip-select-card-trip-1')).toBeInTheDocument();
    expect(screen.getByTestId('trip-select-card-trip-2')).toBeInTheDocument();
  });

  it('navigue vers /journal/:tripId au clic sur un voyage', async () => {
    const user = userEvent.setup();
    (useMyTrips as ReturnType<typeof vi.fn>).mockReturnValue({
      data: [makeTrip('trip-1', 'Voyage Liège'), makeTrip('trip-2', 'Voyage Namur')],
      isLoading: false,
      isError: false,
    });
    render(<Wrapper />);
    await user.click(screen.getByTestId('trip-select-card-trip-1'));
    await waitFor(() => {
      expect(screen.getByTestId('journal-screen')).toBeInTheDocument();
    });
  });

  it('affiche un message et le CTA créer si aucun voyage', () => {
    (useMyTrips as ReturnType<typeof vi.fn>).mockReturnValue({
      data: [],
      isLoading: false,
      isError: false,
    });
    render(<Wrapper />);
    expect(screen.getByTestId('create-trip-cta')).toBeInTheDocument();
    // pas de liste
    expect(screen.queryByTestId('trip-selector-list')).not.toBeInTheDocument();
  });

  it('affiche un message d\'erreur si la requête échoue', () => {
    (useMyTrips as ReturnType<typeof vi.fn>).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
    });
    render(<Wrapper />);
    // EmptyState affiché, pas de liste
    expect(screen.queryByTestId('trip-selector-list')).not.toBeInTheDocument();
    expect(screen.queryByTestId('create-trip-cta')).not.toBeInTheDocument();
  });
});
