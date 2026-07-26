import { describe, it, expect, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import { SyncIndicator } from '../shared/ui/SyncIndicator.tsx';
import '../shared/i18n/i18n.ts';

describe('SyncIndicator', () => {
  beforeEach(() => {
    Object.defineProperty(navigator, 'onLine', { value: true, configurable: true, writable: true });
  });

  it('masque quand online et 0 pending', () => {
    const { container } = render(<SyncIndicator pendingCount={0} />);
    expect(container.firstChild).toBeNull();
  });

  it('visible quand offline', () => {
    Object.defineProperty(navigator, 'onLine', { value: false, configurable: true });
    render(<SyncIndicator />);
    expect(screen.getByRole('status')).toBeInTheDocument();
    expect(screen.getByText(/Hors ligne|Offline/i)).toBeInTheDocument();
  });

  it('visible quand pending', () => {
    render(<SyncIndicator pendingCount={3} />);
    expect(screen.getByRole('status')).toBeInTheDocument();
  });

  it('a aria-live="polite"', () => {
    Object.defineProperty(navigator, 'onLine', { value: false, configurable: true });
    render(<SyncIndicator />);
    expect(screen.getByRole('status')).toHaveAttribute('aria-live', 'polite');
  });
});
