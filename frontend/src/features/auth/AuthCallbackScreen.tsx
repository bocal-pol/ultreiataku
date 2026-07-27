/**
 * AuthCallbackScreen — /auth/callback
 *
 * P0-01 (SEC-ULTREIA-AUTH) — Adapté au flow session cookie.
 *
 * Dans le nouveau flow SSO :
 *   1. SPA → redirectToLogin() → Auth central (?return=/auth/callback)
 *   2. Auth central → backend /admin/sso/callback?code=...&state=...
 *   3. Backend pose le cookie de session HttpOnly (Auth::login + regenerate)
 *   4. Backend redirige vers le frontend → /auth/callback
 *   5. Ce composant vérifie la session via fetchMe() (credentials: 'include')
 *      → 200 : session cookie valide → redirige vers returnPath
 *      → 401 : session absente → affiche erreur
 *
 * Plus aucun token en query param ni en localStorage.
 */

import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { fetchMe } from '../../shared/api/auth.ts';

export function AuthCallbackScreen() {
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const controller = new AbortController();

    // Vérifier la session via cookie (credentials: 'include' dans client.ts)
    fetchMe(controller.signal)
      .then(() => {
        // Session valide — restaurer le chemin de retour stocké avant le login
        const returnPath = sessionStorage.getItem('ultreia_return_path') ?? '/belgique';
        sessionStorage.removeItem('ultreia_return_path');
        navigate(returnPath, { replace: true });
      })
      .catch(() => {
        // 401 : session absente ou expirée
        setError(t('error.session_expired'));
      });

    return () => controller.abort();
  // navigate et t sont stables
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (error) {
    return (
      <div
        role="alert"
        style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          padding: 'var(--space-8)',
          textAlign: 'center',
          gap: 'var(--space-4)',
        }}
      >
        <p style={{ color: 'var(--color-text-secondary)', fontSize: 'var(--font-size-md)' }}>
          {error}
        </p>
        <button
          type="button"
          onClick={() => navigate('/belgique', { replace: true })}
          style={{
            backgroundColor: 'var(--color-interactive-primary)',
            color: 'var(--color-text-inverse)',
            border: 'none',
            borderRadius: 'var(--radius-lg)',
            padding: '12px 24px',
            fontSize: 'var(--font-size-md)',
            cursor: 'pointer',
            minHeight: '44px',
          }}
        >
          {t('error.retry')}
        </button>
      </div>
    );
  }

  return (
    <div
      role="status"
      aria-live="polite"
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        height: '100%',
        color: 'var(--color-text-tertiary)',
        fontSize: 'var(--font-size-md)',
      }}
    >
      {t('auth.connecting')}
    </div>
  );
}
