import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import './shared/i18n/i18n.ts';
import { BottomNav } from './shared/ui/BottomNav.tsx';
import { SyncIndicator } from './shared/ui/SyncIndicator.tsx';
import { StageListScreen } from './features/pilgrimage/stages/StageListScreen.tsx';
import { StageDetailScreen } from './features/pilgrimage/stages/StageDetailScreen.tsx';
import { MapScreen } from './features/pilgrimage/map/MapScreen.tsx';
import { JournalPlaceholder } from './features/pilgrimage/journal/JournalPlaceholder.tsx';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      gcTime: 30 * 60 * 1000,
      retry: 2,
    },
  },
});

function PackPlaceholder() {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      height: '100%', color: 'var(--color-text-tertiary)',
      fontSize: 'var(--font-size-md)', padding: 'var(--space-8)', textAlign: 'center',
    }}>
      Sac — à venir
    </div>
  );
}

function ProfilePlaceholder() {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      height: '100%', color: 'var(--color-text-tertiary)',
      fontSize: 'var(--font-size-md)', padding: 'var(--space-8)', textAlign: 'center',
    }}>
      Profil — à venir
    </div>
  );
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <div style={{
          display: 'flex',
          flexDirection: 'column',
          height: '100dvh',
          overflow: 'hidden',
          position: 'relative',
        }}>
          <SyncIndicator />

          <main style={{ flex: 1, overflow: 'hidden', position: 'relative' }}>
            <Routes>
              <Route path="/" element={<Navigate to="/belgique" replace />} />
              <Route path="/belgique" element={<StageListScreen />} />
              <Route path="/etapes/:code" element={<StageDetailScreen />} />
              <Route path="/carte" element={<MapScreen />} />
              <Route path="/carte/:code" element={<MapScreen />} />
              <Route path="/sac" element={<PackPlaceholder />} />
              <Route path="/sac/:id" element={<PackPlaceholder />} />
              <Route path="/journal" element={<JournalPlaceholder />} />
              <Route path="/journal/:tripId" element={<JournalPlaceholder />} />
              <Route path="/profil" element={<ProfilePlaceholder />} />
            </Routes>
          </main>

          <BottomNav />
        </div>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
