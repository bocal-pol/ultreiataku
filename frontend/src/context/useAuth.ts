/**
 * useAuth — Hook d'accès au contexte d'authentification.
 * Séparé de AuthContext.tsx pour respecter la règle react/only-export-components
 * (Fast Refresh Vite ne tolère pas les fichiers qui mélangent composants et hooks).
 */

import { useContext } from 'react';
import { AuthContext, type AuthContextValue } from './auth-context.ts';

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth doit être utilisé à l\'intérieur de AuthProvider');
  }
  return ctx;
}
