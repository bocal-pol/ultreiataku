/**
 * Tests — TripJoinScreen
 * E2E-20 (accepter/décliner invitation), E2E-23 (token invalide)
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { TripJoinScreen } from '../features/pilgrimage/trip/TripJoinScreen.tsx';

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

// Mock trips hooks
vi.mock('../shared/hooks/useTrips.ts', async () => {
  const actual = await vi.importActual('../shared/hooks/useTrips.ts');
  return {
    ...actual,
    useTripJoinPreview: vi.fn(),
    useJoinByToken: vi.fn(),
  };
});

// Mock AuthContext.tsx (Provider/Guard uniquement)
vi.mock('../context/AuthContext.tsx', () => ({
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthGuard: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

// Mock useAuth (hook séparé depuis Phase C)
vi.mock('../context/useAuth.ts', () => ({
  useAuth: vi.fn(),
}));

import { useTripJoinPreview, useJoinByToken } from '../shared/hooks/useTrips.ts';
import { useAuth } from '../context/useAuth.ts';

// ── Données de test ───────────────────────────────────────────────────────────

const mockPreview = {
  trip: {
    id: 'trip-1',
    name: 'Belgique Mai 2027',
    description: null,
    status: 'planned' as const,
    estimatedStartDate: '2027-05-01',
    estimatedEndDate: null,
    configuration: 'duo' as const,
    isPublic: false,
    inviteToken: 'INVITE_TOKEN',
    route: {
      id: 'r1',
      name: 'Via Mosana',
      totalDistanceKm: 2260,
      stages: [],
    },
    members: [],
    organizer: { id: 'p2', userId: 2, displayName: 'Marie', avatarUrl: null, preferredLocale: 'fr', configuration: 'solo' },
    createdAt: '2027-01-01',
  },
  role: 'participant' as const,
};

const authAuthenticated = {
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
};

const authUnauthenticated = {
  currentUser: null,
  isAuthenticated: false,
  isLoading: false,
  login: vi.fn(),
  logout: vi.fn(),
};

function makeQueryClient() {
  return new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
}

function renderJoinScreen(token = 'VALID_TOKEN') {
  return render(
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={[`/trips/join/${token}`]}>
        <Routes>
          <Route path="/trips/join/:token" element={<TripJoinScreen />} />
          <Route path="/belgique" element={<div>belgique</div>} />
          <Route path="/trips/:id" element={<div>trip dashboard</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('TripJoinScreen — non authentifié', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue({ ...authUnauthenticated, login: vi.fn() });
    (useTripJoinPreview as ReturnType<typeof vi.fn>).mockReturnValue({
      data: undefined, isLoading: false, isError: false,
    });
    (useJoinByToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(), isPending: false, isError: false,
    });
  });

  it('affiche le message de login requis', () => {
    renderJoinScreen();
    expect(screen.getByTestId('trip-join-screen')).toBeInTheDocument();
    expect(screen.getByText('auth.login_required')).toBeInTheDocument();
  });

  it('redirige vers le SSO au clic sur le bouton de connexion', async () => {
    const loginFn = vi.fn();
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue({ ...authUnauthenticated, login: loginFn });

    const user = userEvent.setup();
    renderJoinScreen('MY_TOKEN');
    await user.click(screen.getByText('auth.login_cta'));
    expect(loginFn).toHaveBeenCalledWith('/trips/join/MY_TOKEN');
  });
});

describe('TripJoinScreen — authentifié, token valide', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue(authAuthenticated);
    (useTripJoinPreview as ReturnType<typeof vi.fn>).mockReturnValue({
      data: mockPreview, isLoading: false, isError: false,
    });
    (useJoinByToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(), isPending: false, isError: false,
    });
  });

  it('affiche le résumé du voyage', () => {
    renderJoinScreen();
    expect(screen.getByTestId('trip-join-screen')).toBeInTheDocument();
    expect(screen.getByText('Belgique Mai 2027')).toBeInTheDocument();
    expect(screen.getByText('Marie')).toBeInTheDocument();
  });

  it('affiche les boutons accepter et décliner', () => {
    renderJoinScreen();
    expect(screen.getByTestId('join-accept-btn')).toBeInTheDocument();
    expect(screen.getByTestId('join-decline-btn')).toBeInTheDocument();
  });

  it('appelle la mutation de join au clic sur accepter', async () => {
    const mutateFn = vi.fn();
    (useJoinByToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: mutateFn, isPending: false, isError: false,
    });
    const user = userEvent.setup();
    renderJoinScreen('VALID_TOKEN');

    await user.click(screen.getByTestId('join-accept-btn'));
    expect(mutateFn).toHaveBeenCalledWith(
      { token: 'VALID_TOKEN' },
      expect.any(Object),
    );
  });

  it('redirige vers /belgique au clic sur décliner', async () => {
    const user = userEvent.setup();
    renderJoinScreen();

    await user.click(screen.getByTestId('join-decline-btn'));
    await waitFor(() => {
      expect(screen.getByText('belgique')).toBeInTheDocument();
    });
  });
});

describe('TripJoinScreen — token invalide', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue(authAuthenticated);
    (useTripJoinPreview as ReturnType<typeof vi.fn>).mockReturnValue({
      data: undefined, isLoading: false, isError: true,
    });
    (useJoinByToken as ReturnType<typeof vi.fn>).mockReturnValue({
      mutate: vi.fn(), isPending: false, isError: false,
    });
  });

  it('affiche le message d\'invitation invalide', () => {
    renderJoinScreen('EXPIRED_TOKEN');
    expect(screen.getByRole('alert')).toBeInTheDocument();
    expect(screen.getByText('error.invite_invalid')).toBeInTheDocument();
  });

  it('ne montre pas les boutons accepter/décliner', () => {
    renderJoinScreen('EXPIRED_TOKEN');
    expect(screen.queryByTestId('join-accept-btn')).toBeNull();
    expect(screen.queryByTestId('join-decline-btn')).toBeNull();
  });
});
