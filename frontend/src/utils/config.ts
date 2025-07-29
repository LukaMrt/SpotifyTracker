// Application configuration utilities

export const config = {
  api: {
    baseUrl: import.meta.env.VITE_API_BASE_URL,
    timeout: parseInt(import.meta.env.VITE_API_TIMEOUT, 10),
  },
  app: {
    name: import.meta.env.VITE_APP_NAME,
    version: import.meta.env.VITE_APP_VERSION,
  },
  dev: {
    isDevMode: import.meta.env.VITE_DEV_MODE === 'true',
    showDebug: import.meta.env.VITE_SHOW_DEBUG === 'true',
  },
  spotify: {
    clientId: import.meta.env.VITE_SPOTIFY_CLIENT_ID,
  },
} as const;

// Helper function to check if running in development
export const isDevelopment = () => import.meta.env.DEV;

// Helper function to check if running in production
export const isProduction = () => import.meta.env.PROD;
