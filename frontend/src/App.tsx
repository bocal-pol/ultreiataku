import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import './shared/i18n/i18n.ts';
import { AuthProvider } from './context/AuthContext.tsx';
import { BottomNav } from './shared/ui/BottomNav.tsx';
import { SyncIndicator } from './shared/ui/SyncIndicator.tsx';
import { StageListScreen } from './features/pilgrimage/stages/StageListScreen.tsx';
import { StageDetailScreen } from './features/pilgrimage/stages/StageDetailScreen.tsx';
import { MapScreen } from './features/pilgrimage/map/MapScreen.tsx';
import { JournalPlaceholder } from './features/pilgrimage/journal/JournalPlaceholder.tsx';
import { AuthCallbackScreen } from './features/auth/AuthCallbackScreen.tsx';
import { SplashScreen } from './features/auth/SplashScreen.tsx';
import { MyTripsScreen } from './features/pilgrimage/trip/MyTripsScreen.tsx';
import { TripCreateScreen } from './features/pilgrimage/trip/TripCreateScreen.tsx';
import { TripDashboardScreen } from './features/pilgrimage/trip/TripDashboardScreen.tsx';
import { TripJoinScreen } from './features/pilgrimage/trip/TripJoinScreen.tsx';
import { AuthGuard } from './context/AuthContext.tsx';

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
        <AuthProvider>
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
                {/* Root : SplashScreen si non auth, sinon redirect /belgique */}
                <Route path="/" element={<SplashScreen />} />

                {/* Auth SSO callback */}
                <Route path="/auth/callback" element={<AuthCallbackScreen />} />

                {/* Routes publiques */}
                <Route path="/belgique" element={<StageListScreen />} />
                <Route path="/etapes/:code" element={<StageDetailScreen />} />
                <Route path="/carte" element={<MapScreen />} />
                <Route path="/carte/:code" element={<MapScreen />} />

                {/* Invitation — accessible sans auth (gère redirect login en interne) */}
                <Route path="/trips/join/:token" element={<TripJoinScreen />} />

                {/* Routes protégées — requièrent auth */}
                <Route path="/trips" element={
                  <AuthGuard>
                    <MyTripsScreen />
                  </AuthGuard>
                } />
                <Route path="/trips/new" element={
                  <AuthGuard>
                    <TripCreateScreen />
                  </AuthGuard>
                } />
                <Route path="/trips/:id" element={
                  <AuthGuard>
                    <TripDashboardScreen />
                  </AuthGuard>
                } />

                {/* Sac + Journal + Profil */}
                <Route path="/sac" element={<PackPlaceholder />} />
                <Route path="/sac/:id" element={<PackPlaceholder />} />
                <Route path="/journal" element={<JournalPlaceholder />} />
                <Route path="/journal/:tripId" element={<JournalPlaceholder />} />
                <Route path="/profil" element={<ProfilePlaceholder />} />

                {/* Fallback */}
                <Route path="*" element={<Navigate to="/belgique" replace />} />
              </Routes>
            </main>

            <BottomNav />
          </div>
        </AuthProvider>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
