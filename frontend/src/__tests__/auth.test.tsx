/**
 * Tests — Auth SSO Ultreiataku
 * E2E-17 (AuthCallbackScreen), E2E-18 (SplashScreen)
 */

import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthCallbackScreen } from '../features/auth/AuthCallbackScreen.tsx';
import { SplashScreen } from '../features/auth/SplashScreen.tsx';

// ── Mocks ─────────────────────────────────────────────────────────────────────

vi.mock('../shared/api/auth.ts', () => ({
  fetchMe: vi.fn(),
  redirectToLogin: vi.fn(),
  logout: vi.fn(),
}));

vi.mock('../shared/i18n/i18n.ts', () => ({}));

vi.mock('react-i18next', () => ({
  useTranslation: () => ({
    t: (key: string) => key,
    i18n: { language: 'fr', changeLanguage: vi.fn() },
  }),
  Trans: ({ children }: { children: React.ReactNode }) => children,
}));

// Mock AuthContext pour SplashScreen
vi.mock('../context/AuthContext.tsx', () => ({
  useAuth: vi.fn().mockReturnValue({
    currentUser: null,
    isAuthenticated: false,
    isLoading: false,
    login: vi.fn(),
    logout: vi.fn(),
  }),
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthGuard: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

import { fetchMe, redirectToLogin } from '../shared/api/auth.ts';
import { useAuth } from '../context/AuthContext.tsx';

const mockFetchMe = fetchMe as ReturnType<typeof vi.fn>;
const mockRedirectToLogin = redirectToLogin as ReturnType<typeof vi.fn>;

function makeQueryClient() {
  return new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
}

// Wrapper minimaliste pour AuthCallbackScreen (sans AuthProvider pour éviter double-fetch)
function CallbackWrapper({ url }: { url: string }) {
  return (
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={[url]}>
        <Routes>
          <Route path="/auth/callback" element={<AuthCallbackScreen />} />
          <Route path="/belgique" element={<div data-testid="belgique-page">belgique</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>
  );
}

// ── Tests AuthCallbackScreen ──────────────────────────────────────────────────

describe('AuthCallbackScreen', () => {
  beforeEach(() => {
    localStorage.clear();
    sessionStorage.clear();
    vi.clearAllMocks();
  });

  afterEach(() => {
    localStorage.clear();
    sessionStorage.clear();
  });

  it('affiche le message de connexion pendant le chargement', () => {
    // fetchMe ne resolve jamais = pending state
    mockFetchMe.mockReturnValue(new Promise(() => {}));

    render(<CallbackWrapper url="/auth/callback?token=TEST_TOKEN" />);

    // role="status" visible tant que fetchMe est pending
    expect(screen.getByRole('status')).toBeInTheDocument();
    expect(screen.getByText('auth.connecting')).toBeInTheDocument();
  });

  it('stocke le token et appelle fetchMe', async () => {
    const mockUser = {
      userId: 1,
      name: 'Pascal',
      email: 'pascal@test.be',
      pilgrim: { id: 'p1', userId: 1, displayName: 'Pascal', avatarUrl: null, preferredLocale: 'fr', configuration: 'solo' },
    };
    mockFetchMe.mockResolvedValueOnce(mockUser);

    render(<CallbackWrapper url="/auth/callback?token=MY_TOKEN" />);

    await waitFor(() => {
      expect(mockFetchMe).toHaveBeenCalledOnce();
    });

    expect(localStorage.getItem('ultreia_token')).toBe('MY_TOKEN');
  });

  it('affiche une erreur si aucun token dans l\'URL', async () => {
    render(<CallbackWrapper url="/auth/callback" />);

    await waitFor(() => {
      expect(screen.getByRole('alert')).toBeInTheDocument();
    });
    expect(mockFetchMe).not.toHaveBeenCalled();
  });

  it('affiche erreur et bouton retry si fetchMe échoue', async () => {
    mockFetchMe.mockRejectedValueOnce(new Error('Network error'));

    render(<CallbackWrapper url="/auth/callback?token=BAD_TOKEN" />);

    await waitFor(() => {
      expect(screen.getByRole('alert')).toBeInTheDocument();
    });
    expect(screen.getByText('error.retry')).toBeInTheDocument();
    expect(localStorage.getItem('ultreia_token')).toBeNull();
  });

  it('accepte un code à la place d\'un token', async () => {
    const mockUser = {
      userId: 1,
      name: 'Pascal',
      email: 'pascal@test.be',
      pilgrim: { id: 'p1', userId: 1, displayName: 'Pascal', avatarUrl: null, preferredLocale: 'fr', configuration: 'solo' },
    };
    mockFetchMe.mockResolvedValueOnce(mockUser);

    render(<CallbackWrapper url="/auth/callback?code=CODE_XYZ" />);

    await waitFor(() => expect(mockFetchMe).toHaveBeenCalledOnce());
    expect(localStorage.getItem('ultreia_token')).toBe('CODE_XYZ');
  });
});

// ── Tests SplashScreen ────────────────────────────────────────────────────────

describe('SplashScreen', () => {
  beforeEach(() => {
    localStorage.clear();
    vi.clearAllMocks();

    // Reset useAuth mock avant chaque test
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue({
      currentUser: null,
      isAuthenticated: false,
      isLoading: false,
      login: vi.fn(),
      logout: vi.fn(),
    });
  });

  function renderSplash() {
    return render(
      <QueryClientProvider client={makeQueryClient()}>
        <MemoryRouter>
          <SplashScreen />
        </MemoryRouter>
      </QueryClientProvider>,
    );
  }

  it('affiche le bouton de connexion', () => {
    renderSplash();
    expect(screen.getByTestId('splash-login-btn')).toBeInTheDocument();
    expect(screen.getByText('auth.login_cta')).toBeInTheDocument();
  });

  it('appelle login() au clic sur le bouton de connexion', async () => {
    const loginFn = vi.fn();
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue({
      currentUser: null,
      isAuthenticated: false,
      isLoading: false,
      login: loginFn,
      logout: vi.fn(),
    });

    const user = userEvent.setup();
    renderSplash();

    await user.click(screen.getByTestId('splash-login-btn'));
    expect(loginFn).toHaveBeenCalledOnce();
  });

  it('contient un lien vers la route sans compte', () => {
    renderSplash();
    const link = screen.getByRole('link', { name: /voir la route/i });
    expect(link).toHaveAttribute('href', '/belgique');
  });

  it('affiche le titre Ultreiataku', () => {
    renderSplash();
    expect(screen.getByText('Ultreïataku')).toBeInTheDocument();
  });
});

// Pour satisfaire eslint no-unused-vars
void mockRedirectToLogin;
