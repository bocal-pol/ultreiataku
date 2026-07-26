import '@testing-library/jest-dom';
import { vi } from 'vitest';

// Mock leaflet (DOM-only, jsdom ne supporte pas WebGL)
vi.mock('leaflet', () => ({
  default: {
    map: vi.fn(() => ({
      addLayer: vi.fn(),
      removeLayer: vi.fn(),
      fitBounds: vi.fn(),
      setView: vi.fn(),
      remove: vi.fn(),
      zoomIn: vi.fn(),
      zoomOut: vi.fn(),
    })),
    tileLayer: vi.fn(() => ({ addTo: vi.fn() })),
    polyline: vi.fn(() => ({
      addTo: vi.fn(),
      getBounds: vi.fn(() => ({})),
      setStyle: vi.fn(),
    })),
    circleMarker: vi.fn(() => ({ addTo: vi.fn() })),
    marker: vi.fn(() => ({ addTo: vi.fn(), bindPopup: vi.fn(() => ({ on: vi.fn() })), on: vi.fn() })),
    layerGroup: vi.fn(() => ({ addTo: vi.fn(), clearLayers: vi.fn() })),
    divIcon: vi.fn(() => ({})),
  },
  map: vi.fn(),
}));

// Mock Service Worker
Object.defineProperty(navigator, 'serviceWorker', {
  value: {
    register: vi.fn().mockResolvedValue(undefined),
    ready: Promise.resolve({ sync: { register: vi.fn() } }),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
  },
  writable: true,
});

// Mock geolocation
Object.defineProperty(navigator, 'geolocation', {
  value: {
    getCurrentPosition: vi.fn(),
  },
  writable: true,
});

// Mock IndexedDB — inclut toutes les fonctions vagues 1a→1e
vi.mock('../shared/db/indexeddb.ts', () => ({
  getDb: vi.fn(),
  cacheStage: vi.fn().mockResolvedValue(undefined),
  getCachedStage: vi.fn().mockResolvedValue(undefined),
  cacheGpx: vi.fn().mockResolvedValue(undefined),
  getCachedGpx: vi.fn().mockResolvedValue(null),
  // Journal pending
  getPendingJournalEntries: vi.fn().mockResolvedValue([]),
  getAllJournalEntries: vi.fn().mockResolvedValue([]),
  putPendingJournalEntry: vi.fn().mockResolvedValue(undefined),
  markJournalEntrySynced: vi.fn().mockResolvedValue(undefined),
  getJournalEntry: vi.fn().mockResolvedValue(undefined),
  // Journal photo pending
  getPendingPhotosForEntry: vi.fn().mockResolvedValue([]),
  putPendingPhoto: vi.fn().mockResolvedValue(undefined),
  deletePendingPhoto: vi.fn().mockResolvedValue(undefined),
  gcOrphanPhotos: vi.fn().mockResolvedValue(undefined),
}));

// Cleanup after each test
import { afterEach } from 'vitest';
import { cleanup } from '@testing-library/react';
afterEach(() => {
  cleanup();
  vi.clearAllMocks();
});
