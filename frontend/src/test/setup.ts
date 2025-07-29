// 🧪 CONFIGURATION GLOBALE DES TESTS
import '@testing-library/jest-dom';

// 🔧 Configuration globale des tests
global.ResizeObserver = class ResizeObserver {
  observe() {}
  unobserve() {}
  disconnect() {}
};

// 🌐 Mock des variables d'environnement pour les tests
Object.defineProperty(import.meta, 'env', {
  value: {
    VITE_API_BASE_URL: 'http://localhost:3001/api',
    VITE_APP_NAME: 'Spotify Tracker Test',
    VITE_APP_VERSION: '1.0.0-test',
    VITE_DEV_MODE: 'true',
    VITE_SHOW_DEBUG: 'false',
  },
  writable: true,
});

// 🔧 Fix pour MSW et fetch dans l'environnement de test
import { beforeAll, afterEach, afterAll } from 'vitest';

// Polyfill pour AbortSignal si nécessaire
if (!globalThis.AbortSignal) {
  globalThis.AbortSignal = AbortSignal;
}

if (!globalThis.AbortController) {
  globalThis.AbortController = AbortController;
}
