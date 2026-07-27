/**
 * auth-context.ts — Instance du contexte React pour l'authentification.
 * Fichier .ts (non .tsx) pour ne pas déclencher la règle react/only-export-components
 * qui s'applique aux fichiers JSX uniquement.
 * Importé par AuthContext.tsx (Provider/Guard) et useAuth.ts (hook consommateur).
 */

import { createContext } from 'react';
import type { CurrentUserModel } from '../models/pilgrimage.ts';

export interface AuthContextValue {
  currentUser: CurrentUserModel | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (returnPath?: string) => void;
  logout: () => void;
}

export const AuthContext = createContext<AuthContextValue | null>(null);
