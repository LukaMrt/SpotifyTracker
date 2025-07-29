// 📝 Ce fichier définit TOUTES les requêtes API de notre app
import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';

import { config } from '@/utils/config';

export interface Artist {
  id: string;
  name: string;
}

export interface Track {
  id: string;
  name: string;
  artists: Artist[];
}

export const spotifyApi = createApi({
  reducerPath: 'spotifyApi',
  tagTypes: ['Artist', 'Track'],

  baseQuery: fetchBaseQuery({
    baseUrl: config.api.baseUrl,
    prepareHeaders: headers => {
      headers.set('Content-Type', 'application/json');
      return headers;
    },
  }),

  endpoints: builder => ({
    getArtists: builder.query<Artist[], void>({
      query: () => '/artists',
      providesTags: ['Artist'],
    }),

    getArtist: builder.query<Artist, string>({
      query: id => `/artists/${id}`,
      providesTags: (_result, _error, id) => [{ type: 'Artist', id }],
    }),

    getTracks: builder.query<Track[], void>({
      query: () => '/tracks',
      providesTags: ['Track'],
    }),
  }),
});

export const { useGetArtistsQuery, useGetArtistQuery, useGetTracksQuery } =
  spotifyApi;
