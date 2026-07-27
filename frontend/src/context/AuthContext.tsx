/**
 * AuthContext — Provider SSO Ultreiataku
 *
 * P0-01 (SEC-ULTREIA-AUTH) — Migration Bearer/localStorage → session cookie HttpOnly.
 * L'état d'authentification est déterminé uniquement par la réponse de /api/pilgrimage/me :
 *   - 200 → session cookie valide → utilisateur connecté
 *   - 401 → session absente ou expirée → redirection SSO
 *
 * Plus aucune donnée de session stockée en localStorage.
 * P1-05 résolu par construction.
 *
 * Architecture Fast Refresh :
 * - AuthContext (createContext) → context/auth-context.ts (fichier .ts, hors règle JSX)
 * - useAuth (hook) → context/useAuth.ts
 * - Ce fichier n'exporte que des composants (AuthProvider, AuthGuard).
 */

import {
  useContext,
  useEffect,
  useState,
  useCallback,
  type ReactNode,
} from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchMe, redirectToLogin, logout as authLogout } from '../shared/api/auth.ts';
import { AuthContext } from './auth-context.ts';
import type { CurrentUserModel } from '../models/pilgrimage.ts';

export function AuthProvider({ children }: { children: ReactNode }) {
  const [currentUser, setCurrentUser] = useState<CurrentUserModel | null>(null);
  // Toujours charger au montage : l'état d'auth est dans le cookie, pas en mémoire JS.
  const [isLoading, setIsLoading] = useState<boolean>(true);

  const clearAuth = useCallback(() => {
    setCurrentUser(null);
  }, []);

  // Vérifier la session au montage via /api/pilgrimage/me (credentials: 'include').
  // 200 → session valide, 401 → non connecté.
  useEffect(() => {
    setIsLoading(true);
    const controller = new AbortController();

    fetchMe(controller.signal)
      .then(user => {
        setCurrentUser(user);
      })
      .catch(() => {
        // 401 ou erreur réseau (offline PWA) — utilisateur non connecté.
        setCurrentUser(null);
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, []);

  // Écouter l'événement 401 global du client HTTP (session expirée en cours d'usage).
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
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('AuthGuard doit être utilisé à l\'intérieur de AuthProvider');
  }
  const { isAuthenticated, isLoading, login } = ctx;
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
