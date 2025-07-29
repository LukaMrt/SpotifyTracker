// 🧪 UTILITAIRES DE TEST
import { configureStore } from '@reduxjs/toolkit';
import { setupListeners } from '@reduxjs/toolkit/query';
import { render, type RenderOptions } from '@testing-library/react';
import type { ReactElement } from 'react';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';

import { spotifyApi } from '@/api/spotifyApi';

// 🏪 Store de test avec l'API slice
export const createTestStore = () => {
  const store = configureStore({
    reducer: {
      [spotifyApi.reducerPath]: spotifyApi.reducer,
    },
    middleware: getDefaultMiddleware =>
      getDefaultMiddleware().concat(spotifyApi.middleware),
  });

  setupListeners(store.dispatch);
  return store;
};

// 🎭 Wrapper de test avec tous les providers
interface TestProvidersProps {
  children: React.ReactNode;
  store?: ReturnType<typeof createTestStore>;
}

export const TestProviders = ({
  children,
  store = createTestStore(),
}: TestProvidersProps) => {
  return (
    <Provider store={store}>
      <BrowserRouter>{children}</BrowserRouter>
    </Provider>
  );
};

// 🚀 Fonction de rendu personnalisée
interface CustomRenderOptions extends Omit<RenderOptions, 'wrapper'> {
  store?: ReturnType<typeof createTestStore>;
}

export const renderWithProviders = (
  ui: ReactElement,
  { store = createTestStore(), ...renderOptions }: CustomRenderOptions = {}
) => {
  const Wrapper = ({ children }: { children: React.ReactNode }) => (
    <TestProviders store={store}>{children}</TestProviders>
  );

  return {
    store,
    ...render(ui, { wrapper: Wrapper, ...renderOptions }),
  };
};

// 📊 Mocks RTK Query pour les tests
export const createMockApiResponse = <T,>(
  data: T,
  isLoading = false,
  error: any = null
) => ({
  data,
  isLoading,
  isFetching: isLoading,
  isSuccess: !isLoading && !error,
  isError: !!error,
  error,
  refetch: vi.fn(),
});
