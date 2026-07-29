/**
 * SplashScreen — / (non authentifié)
 * Point d'entrée pour les utilisateurs non connectés.
 * Les utilisateurs connectés sont redirigés vers /belgique depuis App.tsx.
 */

import { useTranslation } from 'react-i18next';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/useAuth.ts';

export function SplashScreen() {
  const { t } = useTranslation('pilgrimage');
  const { login, isAuthenticated, isLoading } = useAuth();

  // Déjà connecté → on ne montre pas l'écran de login, on entre dans l'app.
  if (isAuthenticated) {
    return <Navigate to="/belgique" replace />;
  }

  // Session en cours de vérification → écran neutre (évite le flash « se connecter »).
  if (isLoading) {
    return (
      <div
        role="status"
        aria-live="polite"
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          height: '100%',
          backgroundColor: 'var(--color-bg-base)',
          color: 'var(--color-text-tertiary)',
        }}
      >
        {t('auth.connecting')}
      </div>
    );
  }

  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        height: '100%',
        backgroundColor: 'var(--color-bg-base)',
        padding: 'var(--space-8)',
        textAlign: 'center',
        gap: 'var(--space-6)',
      }}
    >
      {/* Logo */}
      <div>
        <svg
          width="64"
          height="64"
          viewBox="0 0 64 64"
          fill="none"
          aria-hidden="true"
          role="img"
          aria-label="Logo Ultreïataku"
        >
          <circle cx="32" cy="32" r="30" stroke="var(--color-gold-500)" strokeWidth="2" />
          <path
            d="M32 16 C22 28 22 36 32 48 C42 36 42 28 32 16Z"
            fill="var(--color-gold-500)"
            opacity="0.8"
          />
        </svg>
      </div>

      <div>
        <h1
          style={{
            fontSize: '1.5rem',
            fontFamily: 'var(--font-family-display)',
            color: 'var(--color-gold-500)',
            margin: '0 0 var(--space-2) 0',
            letterSpacing: 'var(--letter-spacing-wide)',
          }}
        >
          Ultreïataku
        </h1>
        <p
          style={{
            fontSize: '0.9rem',
            color: 'var(--color-text-secondary)',
            margin: 0,
          }}
        >
          Compagnon du Chemin
        </p>
      </div>

      <hr
        aria-hidden="true"
        style={{
          width: '60px',
          border: 'none',
          borderTop: '1px solid var(--color-gold-500)',
          opacity: 0.4,
        }}
      />

      <button
        type="button"
        data-testid="splash-login-btn"
        onClick={() => login()}
        style={{
          width: '100%',
          maxWidth: '320px',
          minHeight: '56px',
          backgroundColor: 'var(--color-interactive-primary)',
          color: 'var(--color-text-inverse)',
          border: 'none',
          borderRadius: 'var(--radius-lg)',
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          cursor: 'pointer',
          fontFamily: 'var(--font-family-interface)',
        }}
      >
        {t('auth.login_cta')}
      </button>

      <p
        style={{
          fontSize: '0.75rem',
          color: 'var(--color-text-tertiary)',
          margin: 0,
        }}
      >
        Liège → Santiago · ~2 260 km
      </p>

      <a
        href="/belgique"
        style={{
          fontSize: 'var(--font-size-sm)',
          color: 'var(--color-text-secondary)',
          textDecoration: 'underline',
          minHeight: '44px',
          display: 'flex',
          alignItems: 'center',
        }}
      >
        Voir la route sans compte →
      </a>
    </div>
  );
}
