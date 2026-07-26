/**
 * TripCreateScreen — /trips/new
 * Formulaire de création d'un voyage.
 * ULTREIA-36
 */

import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQuery } from '@tanstack/react-query';
import { useCreateTrip } from '../../../shared/hooks/useTrips.ts';
import { apiFetch } from '../../../shared/api/client.ts';
import type { ApiListResponseDto, RouteResponseDto } from '../../../dtos/pilgrimage.ts';
import { mapRoute } from '../../../mappers/pilgrimage.ts';

export function TripCreateScreen() {
  const navigate = useNavigate();
  const { t } = useTranslation('pilgrimage');
  const createMutation = useCreateTrip();

  const [name, setName] = useState('');
  const [routeId, setRouteId] = useState('');
  const [startDate, setStartDate] = useState('');
  const [validationError, setValidationError] = useState<string | null>(null);

  // Charger les routes disponibles
  const { data: routes } = useQuery({
    queryKey: ['routes'],
    queryFn: async ({ signal }) => {
      const resp = await apiFetch<ApiListResponseDto<RouteResponseDto>>('/routes', { signal });
      return resp.data.map(mapRoute);
    },
    staleTime: 30 * 60 * 1000,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setValidationError(null);

    if (!name.trim()) {
      setValidationError(t('trip.name_label') + ' requis');
      return;
    }
    if (!routeId) {
      setValidationError(t('trip.route_label') + ' requise');
      return;
    }

    createMutation.mutate(
      {
        name: name.trim(),
        route_id: routeId,
        estimated_start_date: startDate || null,
        configuration: 'solo',
        description: null,
      },
      {
        onSuccess: (trip) => {
          navigate(`/trips/${trip.id}`, { replace: true });
        },
        onError: () => {
          setValidationError(t('error.sync_failed'));
        },
      },
    );
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%', backgroundColor: 'var(--color-bg-base)' }}>
      {/* Header */}
      <header style={{
        position: 'sticky', top: 0, zIndex: 100,
        backgroundColor: 'var(--color-bg-elevated)',
        borderBottom: '1px solid var(--color-border-subtle)',
        padding: '0 var(--space-4)',
        flexShrink: 0,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', height: '56px', gap: 'var(--space-3)' }}>
          <button
            type="button"
            onClick={() => navigate(-1)}
            aria-label={t('journal.cancel')}
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              color: 'var(--color-text-accent)', padding: '8px', borderRadius: 'var(--radius-md)',
              display: 'flex', alignItems: 'center', minWidth: '44px', minHeight: '44px', justifyContent: 'center',
            }}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>
          <h1 style={{
            fontSize: 'var(--font-size-md)',
            fontWeight: 'var(--font-weight-semibold)',
            color: 'var(--color-text-primary)',
            margin: 0,
            fontFamily: 'var(--font-family-interface)',
          }}>
            {t('trip.create_title')}
          </h1>
        </div>
      </header>

      {/* Formulaire */}
      <div style={{ flex: 1, overflowY: 'auto', padding: 'var(--space-4)' }}>
        <form
          data-testid="trip-create-form"
          onSubmit={handleSubmit}
          noValidate
          style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-5)' }}
        >
          {/* Erreur de validation */}
          {validationError && (
            <div
              role="alert"
              style={{
                backgroundColor: 'rgba(232,80,58,0.1)',
                borderRadius: 'var(--radius-md)',
                padding: 'var(--space-3)',
                fontSize: 'var(--font-size-sm)',
                color: 'var(--color-error, #e8503a)',
                border: '1px solid rgba(232,80,58,0.25)',
              }}
            >
              {validationError}
            </div>
          )}

          {/* Nom du voyage */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-2)' }}>
            <label
              htmlFor="trip-name"
              style={{
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-medium)',
                color: 'var(--color-text-secondary)',
              }}
            >
              {t('trip.name_label')} *
            </label>
            <input
              id="trip-name"
              type="text"
              data-testid="trip-name-input"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
              aria-required="true"
              placeholder="Belgique Mai 2027"
              style={{
                width: '100%',
                minHeight: '52px',
                backgroundColor: 'var(--color-bg-elevated)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-md)',
                padding: '0 var(--space-3)',
                fontSize: 'var(--font-size-md)',
                color: 'var(--color-text-primary)',
                fontFamily: 'var(--font-family-interface)',
                boxSizing: 'border-box',
              }}
            />
          </div>

          {/* Route */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-2)' }}>
            <label
              htmlFor="trip-route"
              style={{
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-medium)',
                color: 'var(--color-text-secondary)',
              }}
            >
              {t('trip.route_label')} *
            </label>
            <select
              id="trip-route"
              data-testid="trip-route-select"
              value={routeId}
              onChange={(e) => setRouteId(e.target.value)}
              required
              aria-required="true"
              style={{
                width: '100%',
                minHeight: '52px',
                backgroundColor: 'var(--color-bg-elevated)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-md)',
                padding: '0 var(--space-3)',
                fontSize: 'var(--font-size-md)',
                color: routeId ? 'var(--color-text-primary)' : 'var(--color-text-tertiary)',
                fontFamily: 'var(--font-family-interface)',
                boxSizing: 'border-box',
                appearance: 'none',
              }}
            >
              <option value="" disabled>{t('trip.route_label')}</option>
              {routes?.map(route => (
                <option key={route.id} value={route.id}>{route.name}</option>
              ))}
            </select>
          </div>

          {/* Date de départ estimée */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-2)' }}>
            <label
              htmlFor="trip-start-date"
              style={{
                fontSize: 'var(--font-size-sm)',
                fontWeight: 'var(--font-weight-medium)',
                color: 'var(--color-text-secondary)',
              }}
            >
              {t('trip.start_date_label')}
            </label>
            <input
              id="trip-start-date"
              type="date"
              data-testid="trip-start-date-input"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              style={{
                width: '100%',
                minHeight: '52px',
                backgroundColor: 'var(--color-bg-elevated)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-md)',
                padding: '0 var(--space-3)',
                fontSize: 'var(--font-size-md)',
                color: 'var(--color-text-primary)',
                fontFamily: 'var(--font-family-interface)',
                boxSizing: 'border-box',
              }}
            />
          </div>

          {/* Submit */}
          <button
            type="submit"
            data-testid="trip-create-submit"
            disabled={createMutation.isPending}
            aria-busy={createMutation.isPending}
            style={{
              width: '100%',
              minHeight: '56px',
              backgroundColor: 'var(--color-interactive-primary)',
              color: 'var(--color-text-inverse)',
              border: 'none',
              borderRadius: 'var(--radius-lg)',
              fontSize: 'var(--font-size-md)',
              fontWeight: 'var(--font-weight-semibold)',
              cursor: createMutation.isPending ? 'wait' : 'pointer',
              opacity: createMutation.isPending ? 0.7 : 1,
              fontFamily: 'var(--font-family-interface)',
              marginTop: 'var(--space-2)',
            }}
          >
            {createMutation.isPending ? '…' : t('trip.new_trip')}
          </button>
        </form>
      </div>
    </div>
  );
}
