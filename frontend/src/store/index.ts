// 🏪 STORE REDUX PRINCIPAL
// C'est le "cerveau" de notre application qui stocke tout l'état

import { configureStore } from '@reduxjs/toolkit';

import { spotifyApi } from '@/api/spotifyApi';

export const store = configureStore({
  reducer: {
    [spotifyApi.reducerPath]: spotifyApi.reducer,
  },

  middleware: getDefaultMiddleware =>
    getDefaultMiddleware().concat(spotifyApi.middleware),
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
