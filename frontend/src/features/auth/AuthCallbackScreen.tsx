/**
 * AuthCallbackScreen — /auth/callback
 * Reçoit le token ou code depuis le query param SSO.
 * Stocke le token, fetch le user, puis redirige.
 */

import { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { fetchMe } from '../../shared/api/auth.ts';

export function AuthCallbackScreen() {
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const [searchParams] = useSearchParams();
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const token = searchParams.get('token');
    const code = searchParams.get('code');

    if (!token && !code) {
      setError(t('error.session_expired'));
      return;
    }

    if (token) {
      localStorage.setItem('ultreia_token', token);
    } else if (code) {
      // Pour le flow code — le code est un token directement (backend exchange déjà fait)
      localStorage.setItem('ultreia_token', code);
    }

    const controller = new AbortController();
    fetchMe(controller.signal)
      .then(user => {
        localStorage.setItem('ultreia_user', JSON.stringify(user));
        // Restaurer le chemin de retour stocké avant le login
        const returnPath = sessionStorage.getItem('ultreia_return_path') ?? '/belgique';
        sessionStorage.removeItem('ultreia_return_path');
        navigate(returnPath, { replace: true });
      })
      .catch(() => {
        localStorage.removeItem('ultreia_token');
        setError(t('error.session_expired'));
      });

    return () => controller.abort();
  // searchParams est stable dans l'effect initial, navigate et t aussi
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
