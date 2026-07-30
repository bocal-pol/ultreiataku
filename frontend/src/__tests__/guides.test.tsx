/**
 * Tests — Guides du Chemin
 *
 * Couvre :
 * 1. GuidesScreen affiche les sections par catégorie
 * 2. GuidesScreen affiche l'état vide
 * 3. GuidesScreen affiche le skeleton pendant le chargement
 * 4. GuideDetailScreen affiche le contenu markdown
 * 5. Bouton logout présent dans ProfileScreen quand connecté
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

// ── Mocks ─────────────────────────────────────────────────────────────────────

vi.mock('../shared/i18n/i18n.ts', () => ({}));

vi.mock('react-i18next', () => ({
  useTranslation: () => ({
    t: (key: string) => key,
    i18n: { language: 'fr', changeLanguage: vi.fn() },
  }),
  Trans: ({ children }: { children: React.ReactNode }) => children,
}));

vi.mock('../context/AuthContext.tsx', () => ({
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  AuthGuard: ({ children }: { children: React.ReactNode }) => <>{children}</>,
}));

vi.mock('../context/useAuth.ts', () => ({
  useAuth: vi.fn().mockReturnValue({
    currentUser: {
      userId: 1,
      name: 'Pascal',
      email: 'pascal@test.be',
      pilgrim: {
        id: 'p1',
        userId: 1,
        displayName: 'Pascal',
        avatarUrl: null,
        preferredLocale: 'fr',
        configuration: 'solo',
      },
    },
    isAuthenticated: true,
    isLoading: false,
    login: vi.fn(),
    logout: vi.fn(),
  }),
}));

vi.mock('../shared/hooks/useTrips.ts', () => ({
  useMyTrips: vi.fn().mockReturnValue({ data: [], isLoading: false }),
}));

// Mock des hooks guides (pattern identique à TripDashboard.test.tsx)
vi.mock('../shared/hooks/useGuides.ts', () => ({
  useGuides: vi.fn(),
  useGuideDetail: vi.fn(),
}));

import { useAuth } from '../context/useAuth.ts';
import { useGuides, useGuideDetail } from '../shared/hooks/useGuides.ts';
import { GuidesScreen } from '../features/pilgrimage/guides/GuidesScreen.tsx';
import { GuideDetailScreen } from '../features/pilgrimage/guides/GuideDetailScreen.tsx';
import { ProfileScreen } from '../features/pilgrimage/profile/ProfileScreen.tsx';

const mockUseGuides = useGuides as ReturnType<typeof vi.fn>;
const mockUseGuideDetail = useGuideDetail as ReturnType<typeof vi.fn>;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeQueryClient() {
  return new QueryClient({
    defaultOptions: {
      queries: { retry: false, staleTime: 0 },
      mutations: { retry: false },
    },
  });
}

function renderGuidesList() {
  return render(
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={['/guides']}>
        <Routes>
          <Route path="/guides" element={<GuidesScreen />} />
          <Route path="/guides/:slug" element={<GuideDetailScreen />} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

function renderGuideDetail(slug: string) {
  return render(
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={[`/guides/${slug}`]}>
        <Routes>
          <Route path="/guides/:slug" element={<GuideDetailScreen />} />
          <Route path="/guides" element={<div>guides-list</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

function renderProfile() {
  return render(
    <QueryClientProvider client={makeQueryClient()}>
      <MemoryRouter initialEntries={['/profil']}>
        <Routes>
          <Route path="/profil" element={<ProfileScreen />} />
          <Route path="/" element={<div>splash</div>} />
          <Route path="/guides" element={<div>guides</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

// ── Tests GuidesScreen ────────────────────────────────────────────────────────

describe('GuidesScreen', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('affiche le skeleton pendant le chargement', () => {
    mockUseGuides.mockReturnValue({ data: undefined, isLoading: true, isError: false, refetch: vi.fn() });
    renderGuidesList();
    // Les skeletons sont aria-hidden mais présents dans le DOM
    const skeletons = document.querySelectorAll('[aria-hidden="true"]');
    expect(skeletons.length).toBeGreaterThan(0);
  });

  it('affiche les sections groupées par catégorie', async () => {
    mockUseGuides.mockReturnValue({
      data: [
        { slug: 'forme-physique', category: 'Le Corps', title: 'Forme physique', icon: '🏃' },
        { slug: 'sante-pieds', category: 'Le Corps', title: 'Santé des pieds', icon: '🦶' },
        { slug: 'budget', category: 'Pratique', title: 'Budget du Chemin', icon: '💰' },
      ],
      isLoading: false,
      isError: false,
      refetch: vi.fn(),
    });

    renderGuidesList();

    // Les deux catégories doivent apparaître comme titres de section (h2)
    expect(screen.getByText('Le Corps')).toBeInTheDocument();
    expect(screen.getByText('Pratique')).toBeInTheDocument();

    // Les titres des guides doivent être visibles
    expect(screen.getByText('Forme physique')).toBeInTheDocument();
    expect(screen.getByText('Santé des pieds')).toBeInTheDocument();
    expect(screen.getByText('Budget du Chemin')).toBeInTheDocument();
  });

  it('affiche l\'état vide si aucun guide disponible', () => {
    mockUseGuides.mockReturnValue({ data: [], isLoading: false, isError: false, refetch: vi.fn() });

    renderGuidesList();

    // EmptyState a role="status"
    expect(screen.getByRole('status')).toBeInTheDocument();
    expect(screen.getByText('guides.empty')).toBeInTheDocument();
  });

  it('affiche le message d\'erreur si le chargement échoue', () => {
    mockUseGuides.mockReturnValue({ data: undefined, isLoading: false, isError: true, refetch: vi.fn() });

    renderGuidesList();

    expect(screen.getByRole('alert')).toBeInTheDocument();
    expect(screen.getByText('guides.error')).toBeInTheDocument();
    expect(screen.getByText('error.retry')).toBeInTheDocument();
  });

  it('navigue vers le détail au clic sur une carte guide', async () => {
    mockUseGuides.mockReturnValue({
      data: [
        { slug: 'budget', category: 'Pratique', title: 'Budget du Chemin', icon: '💰' },
      ],
      isLoading: false,
      isError: false,
      refetch: vi.fn(),
    });
    // Le détail utilisera ce mock
    mockUseGuideDetail.mockReturnValue({
      data: {
        slug: 'budget',
        category: 'Pratique',
        title: 'Budget du Chemin',
        icon: '💰',
        content: '# Budget\n\nPrévois 30€/jour.',
      },
      isLoading: false,
      isError: false,
    });

    const user = userEvent.setup();
    renderGuidesList();

    await user.click(screen.getByRole('button', { name: 'Budget du Chemin' }));

    // Après navigation, le détail doit s'afficher
    await waitFor(() => {
      expect(screen.getByTestId('guide-detail-screen')).toBeInTheDocument();
    });
  });
});

// ── Tests GuideDetailScreen ───────────────────────────────────────────────────

describe('GuideDetailScreen', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('affiche le contenu markdown du guide', () => {
    mockUseGuideDetail.mockReturnValue({
      data: {
        slug: 'forme-physique',
        category: 'Le Corps',
        title: 'Forme physique',
        icon: '🏃',
        content: '## Introduction\n\nPrépare ton corps **avant** le départ.\n\n- Marche 30 min/jour\n- Renforce les chevilles',
      },
      isLoading: false,
      isError: false,
    });

    renderGuideDetail('forme-physique');

    expect(screen.getByTestId('guide-detail-screen')).toBeInTheDocument();

    // Titre du guide (h1 dans l'article)
    expect(screen.getByRole('heading', { level: 1, name: 'Forme physique' })).toBeInTheDocument();

    // Contenu markdown rendu — titre h2
    expect(screen.getByRole('heading', { level: 2, name: 'Introduction' })).toBeInTheDocument();

    // Texte en gras rendu dans <strong>
    const strong = document.querySelector('strong');
    expect(strong?.textContent).toBe('avant');

    // Éléments de liste
    expect(screen.getByText(/Marche 30 min\/jour/)).toBeInTheDocument();
  });

  it('affiche le bouton retour vers /guides', () => {
    mockUseGuideDetail.mockReturnValue({
      data: {
        slug: 'budget',
        category: 'Pratique',
        title: 'Budget du Chemin',
        icon: '💰',
        content: '# Budget\n\nPrévois environ 30€/jour.',
      },
      isLoading: false,
      isError: false,
    });

    renderGuideDetail('budget');

    const backLink = screen.getByRole('link', { name: /guides\.back/i });
    expect(backLink).toHaveAttribute('href', '/guides');
  });

  it('affiche le skeleton pendant le chargement', () => {
    mockUseGuideDetail.mockReturnValue({ data: undefined, isLoading: true, isError: false });

    renderGuideDetail('inexistant');

    const skeletons = document.querySelectorAll('[aria-hidden="true"]');
    expect(skeletons.length).toBeGreaterThan(0);
  });

  it('affiche une erreur si le guide n\'existe pas', () => {
    mockUseGuideDetail.mockReturnValue({ data: undefined, isLoading: false, isError: true });

    renderGuideDetail('inexistant');

    expect(screen.getByRole('alert')).toBeInTheDocument();
  });
});

// ── Tests ProfileScreen — bouton logout et administration ─────────────────────

describe('ProfileScreen — logout et administration', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue({
      currentUser: {
        userId: 1,
        name: 'Pascal',
        email: 'pascal@test.be',
        pilgrim: {
          id: 'p1',
          userId: 1,
          displayName: 'Pascal',
          avatarUrl: null,
          preferredLocale: 'fr',
          configuration: 'solo',
        },
      },
      isAuthenticated: true,
      isLoading: false,
      login: vi.fn(),
      logout: vi.fn(),
    });
  });

  it('affiche le bouton "Quitter le Chemin" quand connecté', () => {
    renderProfile();
    expect(screen.getByTestId('profile-logout-btn')).toBeInTheDocument();
    expect(screen.getByText('auth.logout_cta')).toBeInTheDocument();
  });

  it('appelle logout() au clic sur le bouton de déconnexion', async () => {
    const logoutFn = vi.fn();
    (useAuth as ReturnType<typeof vi.fn>).mockReturnValue({
      currentUser: {
        userId: 1,
        name: 'Pascal',
        email: 'pascal@test.be',
        pilgrim: {
          id: 'p1',
          userId: 1,
          displayName: 'Pascal',
          avatarUrl: null,
          preferredLocale: 'fr',
          configuration: 'solo',
        },
      },
      isAuthenticated: true,
      isLoading: false,
      login: vi.fn(),
      logout: logoutFn,
    });

    const user = userEvent.setup();
    renderProfile();

    await user.click(screen.getByTestId('profile-logout-btn'));
    expect(logoutFn).toHaveBeenCalledOnce();
  });

  it('affiche le lien Administration avec target="_blank"', () => {
    renderProfile();
    const adminLink = screen.getByTestId('profile-admin-link');
    expect(adminLink).toBeInTheDocument();
    expect(adminLink).toHaveAttribute('target', '_blank');
    expect(adminLink).toHaveAttribute('rel', 'noopener noreferrer');
  });

  it('affiche le lien vers les guides depuis le profil', () => {
    renderProfile();
    const guidesLink = screen.getByText('profile.guides_link');
    expect(guidesLink).toBeInTheDocument();
  });
});
