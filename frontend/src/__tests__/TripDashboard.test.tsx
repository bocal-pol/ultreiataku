/**
 * Tests — TripDashboardScreen + DepartureForm + InviteDialog
 * E2E-19 (trip dashboard), E2E-21 (departure form), E2E-22 (invite)
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { TripDashboardScreen } from '../features/pilgrimage/trip/TripDashboardScreen.tsx';

// ── Mocks ─────────────────────────────────────────────────────────────────────

vi.mock('../shared/i18n/i18n.ts', () => ({}));

vi.mock('react-i18next', () => ({
  useTranslation: () => ({
    t: (key: string, opts?: Record<string, unknown>) => {
      if (opts && typeof opts === 'object') {
        return Object.entries(opts).reduce((s, [k, v]) => s.replace(`{{${k}}}`, String(v)), key);
      }
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

// Mock des hooks TanStack Query
vi.mock('../shared/hooks/useTrips.ts', async () => {
  const actual = await vi.importActual('../shared/hooks/useTrips.ts');
  return {
    ...actual,
    useTripDetail: vi.fn(),
    useGenerateInviteToken: vi.fn(),
    useRevokeInviteToken: vi.fn(),
    useAddDeparture: vi.fn(),
    useOccupancy: vi.fn(() => ({ data: [], isLoading: false, isError: false })),
  };
});

import {
  useTripDetail,
  useGenerateInviteToken,
  useRevokeInviteToken,
  useAddDeparture,
} from '../shared/hooks/useTrips.ts';

// Mock AuthContext
vi.mock('../context/AuthContext.tsx', () => ({
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
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthGuard: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

// ── Données de test ───────────────────────────────────────────────────────────

const mockTrip = {
  id: 'trip-1',
  name: 'Belgique Mai 2027',
  description: null,
  status: 'planned' as const,
  estimatedStartDate: '2027-05-01',
  estimatedEndDate: null,
  configuration: 'duo' as const,
  isPublic: false,
  inviteToken: null,
  route: {
    id: 'route-1',
    name: 'Via Mosana',
    totalDistanceKm: 2260,
    stages: [
      { id: 's1', code: 'LGE-01', name: 'Liège → Huy', dayNumber: 1 },
      { id: 's2', code: 'LGE-02', name: 'Huy → Namur', dayNumber: 2 },
    ],
  },
  members: [
    {
      pilgrim: { id: 'p1', userId: 1, displayName: 'Pascal', avatarUrl: null, preferredLocale: 'fr', configuration: 'solo' },
      role: 'organizer' as const,
      joinedAt: '2027-01-01',
    },
  ],
  organizer: { id: 'p1', userId: 1, displayName: 'Pascal', avatarUrl: null, preferredLocale: 'fr', configuration: 'solo' },
  createdAt: '2027-01-01',
};

function makeQueryClient() {
  return new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
}

function Wrapper({ tripId = 'trip-1' }) {
  return (
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={[`/trips/${tripId}`]}>
        <Routes>
          <Route path="/trips/:id" element={<TripDashboardScreen />} />
          <Route path="/trips" element={<div>Liste</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>
  );
}

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('TripDashboardScreen', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    (useTripDetail as ReturnType<typeof vi.fn>).mockReturnValue({
      data: mockTrip,
      isLoading: false,
      isError: false,
    });
    (useGenerateInviteToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
      isError: false,
      data: undefined,
      reset: vi.fn(),
    });
    (useRevokeInviteToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
    });
    (useAddDeparture as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
      isError: false,
    });
  });

  it('affiche le dashboard avec le nom du voyage', () => {
    render(<Wrapper />);
    expect(screen.getByTestId('trip-dashboard')).toBeInTheDocument();
    expect(screen.getByText('Belgique Mai 2027')).toBeInTheDocument();
  });

  it('affiche la liste des membres', () => {
    render(<Wrapper />);
    const membersList = screen.getByTestId('members-list');
    expect(membersList).toBeInTheDocument();
    expect(membersList).toHaveAttribute('role', 'list');
    expect(screen.getByText('Pascal')).toBeInTheDocument();
  });

  it('affiche le bouton Inviter pour un organisateur', () => {
    render(<Wrapper />);
    expect(screen.getByTestId('invite-btn')).toBeInTheDocument();
  });

  it('ouvre le dialog d\'invitation au clic sur Inviter', async () => {
    const user = userEvent.setup();
    render(<Wrapper />);
    await user.click(screen.getByTestId('invite-btn'));
    expect(screen.getByTestId('invite-dialog')).toBeInTheDocument();
  });

  it('affiche le formulaire de départ pour l\'organisateur', () => {
    render(<Wrapper />);
    expect(screen.getByTestId('departure-form')).toBeInTheDocument();
    expect(screen.getByTestId('departure-date')).toBeInTheDocument();
    expect(screen.getByTestId('departure-start-stage')).toBeInTheDocument();
    expect(screen.getByTestId('departure-end-stage')).toBeInTheDocument();
  });

  it('affiche un skeleton pendant le chargement', () => {
    (useTripDetail as ReturnType<typeof vi.fn>).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
    });
    const { container } = render(<Wrapper />);
    // skeleton visible (pas de trip-dashboard)
    expect(container.querySelector('[data-testid="trip-dashboard"]')).toBeNull();
  });
});

// ── Tests InviteDialog ────────────────────────────────────────────────────────

describe('InviteDialog — token affiché après génération', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    (useTripDetail as ReturnType<typeof vi.fn>).mockReturnValue({
      data: mockTrip,
      isLoading: false,
      isError: false,
    });
    (useRevokeInviteToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
    });
    (useAddDeparture as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
      isError: false,
    });
  });

  it('affiche les boutons copier et révoquer quand le token existe', async () => {
    const user = userEvent.setup();

    (useGenerateInviteToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
      isError: false,
      data: 'INVITE_TOKEN_XYZ',
      reset: vi.fn(),
    });

    render(<Wrapper />);
    await user.click(screen.getByTestId('invite-btn'));

    expect(screen.getByTestId('invite-token-display')).toBeInTheDocument();
    expect(screen.getByTestId('invite-copy-btn')).toBeInTheDocument();
    expect(screen.getByTestId('invite-revoke-btn')).toBeInTheDocument();
    expect(screen.getByTestId('invite-token-display')).toHaveTextContent('INVITE_TOKEN_XYZ');
  });
});

// ── Tests DepartureForm ───────────────────────────────────────────────────────

describe('DepartureForm', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    (useTripDetail as ReturnType<typeof vi.fn>).mockReturnValue({
      data: mockTrip,
      isLoading: false,
      isError: false,
    });
    (useGenerateInviteToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
      isError: false,
      data: undefined,
      reset: vi.fn(),
    });
    (useRevokeInviteToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(),
      isPending: false,
    });
  });

  it('soumet le formulaire avec des données valides', async () => {
    const mutateFn = vi.fn();
    (useAddDeparture as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: mutateFn,
      isPending: false,
      isError: false,
    });

    const user = userEvent.setup();
    render(<Wrapper />);

    await user.type(screen.getByTestId('departure-date'), '2027-05-01');
    await user.selectOptions(screen.getByTestId('departure-start-stage'), 's1');
    await user.selectOptions(screen.getByTestId('departure-end-stage'), 's2');
    await user.click(screen.getByTestId('departure-submit'));

    expect(mutateFn).toHaveBeenCalledWith(
      expect.objectContaining({
        start_stage_id: 's1',
        end_stage_id: 's2',
        planned_start_date: '2027-05-01',
      }),
      expect.any(Object),
    );
  });
});
