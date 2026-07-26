/**
 * AuthContext — Provider SSO Ultreiataku
 * Token Bearer dans localStorage('ultreia_token')
 * Pilgrim dans localStorage('ultreia_user') pour la persistance inter-sessions
 */

import {
  createContext,
  useContext,
  useEffect,
  useState,
  useCallback,
  type ReactNode,
} from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchMe, redirectToLogin, logout as authLogout } from '../shared/api/auth.ts';
import type { CurrentUserModel } from '../models/pilgrimage.ts';

interface AuthContextValue {
  currentUser: CurrentUserModel | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (returnPath?: string) => void;
  logout: () => void;
}

const AuthContext = createContext<AuthContextValue | null>(null);

function loadCachedUser(): CurrentUserModel | null {
  try {
    const raw = localStorage.getItem('ultreia_user');
    if (!raw) return null;
    return JSON.parse(raw) as CurrentUserModel;
  } catch {
    return null;
  }
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const [currentUser, setCurrentUser] = useState<CurrentUserModel | null>(loadCachedUser);
  const [isLoading, setIsLoading] = useState<boolean>(() => {
    // Seulement charger si token présent
    return localStorage.getItem('ultreia_token') !== null && loadCachedUser() === null;
  });

  const clearAuth = useCallback(() => {
    setCurrentUser(null);
    localStorage.removeItem('ultreia_token');
    localStorage.removeItem('ultreia_user');
  }, []);

  // Charger le user courant si token présent et user non en cache
  useEffect(() => {
    const token = localStorage.getItem('ultreia_token');
    if (!token) {
      setIsLoading(false);
      return;
    }

    // Si déjà en cache, pas besoin de fetcher
    if (currentUser !== null) {
      setIsLoading(false);
      return;
    }

    setIsLoading(true);
    const controller = new AbortController();

    fetchMe(controller.signal)
      .then(user => {
        setCurrentUser(user);
        localStorage.setItem('ultreia_user', JSON.stringify(user));
      })
      .catch(() => {
        // 401 ou erreur réseau — ne pas déconnecter si offline
        clearAuth();
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Écouter l'événement 401 global du client HTTP
  useEffect(() => {
    const handler = () => {
      clearAuth();
    };
    window.addEventListener('ultreia:unauthorized', handler);
    return () => window.removeEventListener('ultreia:unauthorized', handler);
  }, [clearAuth]);

  const login = useCallback((returnPath?: string) => {
    redirectToLogin(returnPath);
  }, []);

  const logout = useCallback(() => {
    clearAuth();
    authLogout();
  }, [clearAuth]);

  return (
    <AuthContext.Provider
      value={{
        currentUser,
        isAuthenticated: currentUser !== null,
        isLoading,
        login,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth doit être utilisé à l\'intérieur de AuthProvider');
  }
  return ctx;
}

/**
 * AuthGuard — protège les routes qui exigent une authentification.
 * Redirige vers le SSO si non connecté, affiche un spinner pendant le chargement.
 */
interface AuthGuardProps {
  children: ReactNode;
  /** Si true, redirige vers SSO ; si false, rend null (usage optionnel) */
  redirectIfUnauthenticated?: boolean;
}

export function AuthGuard({ children, redirectIfUnauthenticated = true }: AuthGuardProps) {
  const { isAuthenticated, isLoading, login } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!isLoading && !isAuthenticated && redirectIfUnauthenticated) {
      login(window.location.pathname + window.location.search);
    }
  }, [isLoading, isAuthenticated, redirectIfUnauthenticated, login, navigate]);

  if (isLoading) {
    return (
      <div
        role="status"
        aria-label="Chargement de la session"
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          color: 'var(--color-text-tertiary)',
          fontSize: 'var(--font-size-md)',
        }}
      >
        <span aria-hidden="true" style={{ marginRight: '8px' }}>⏳</span>
        Chargement…
      </div>
    );
  }

  if (!isAuthenticated && redirectIfUnauthenticated) {
    return null;
  }

  return <>{children}</>;
}
