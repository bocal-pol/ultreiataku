import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

const PORT = 5181;

/**
 * Vite config Ultreiataku — SPA React 19 + TanStack Query + Leaflet.
 *
 * Le backend Laravel Ultreiataku tourne sur le port 8096 :
 *   /api, /storage  →  http://localhost:8096 (ultreiataku-app)
 *
 * usePolling obligatoire : Docker Desktop Windows ne propage pas
 * les événements inotify (cf. feedback_vite_polling_docker).
 */
export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  server: {
    host: '0.0.0.0',
    port: PORT,
    watch: {
      usePolling: true,
      interval: 300,
    },
    proxy: {
      '/api': {
        target: process.env['API_TARGET'] ?? 'http://localhost:8096',
        changeOrigin: true,
      },
      '/storage': {
        target: process.env['API_TARGET'] ?? 'http://localhost:8096',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules/react') || id.includes('node_modules/react-dom')) {
            return 'react';
          }
          if (id.includes('node_modules/react-router')) {
            return 'router';
          }
          if (id.includes('node_modules/@tanstack')) {
            return 'query';
          }
          if (id.includes('node_modules/leaflet')) {
            return 'leaflet';
          }
          if (id.includes('node_modules/i18next') || id.includes('node_modules/react-i18next')) {
            return 'i18n';
          }
        },
      },
    },
  }
});
