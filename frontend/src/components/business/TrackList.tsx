// 🎵 TRACK LIST COMPONENT - Liste de tracks réutilisable
import type { HTMLAttributes, ReactNode } from 'react';
import { Link } from 'react-router-dom';

import type { Track } from '@/api/spotifyApi';
import {
  Card,
  CardContent,
  Button,
  Typography,
  Skeleton,
} from '@/components/ui';
import { cn } from '@/utils/cn';

// 🔗 Interface pour les props du TrackList
interface TrackListProps extends HTMLAttributes<HTMLDivElement> {
  tracks: Track[];
  loading?: boolean;
  error?: string;
  variant?: 'default' | 'compact' | 'detailed';
  showArtists?: boolean;
  showActions?: boolean;
  emptyMessage?: string;
  emptyIcon?: ReactNode;
  onTrackPlay?: (track: Track) => void;
  onTrackFavorite?: (track: Track) => void;
  favoriteTrackIds?: string[];
}

// 🎵 Composant TrackList principal
export const TrackList = ({
  tracks,
  loading = false,
  error,
  variant = 'default',
  showArtists = true,
  showActions = true,
  emptyMessage = 'Aucune piste trouvée',
  emptyIcon,
  onTrackPlay,
  onTrackFavorite,
  favoriteTrackIds = [],
  className,
  ...props
}: TrackListProps) => {
  // 🔄 État de chargement
  if (loading) {
    return (
      <div className={cn('space-y-3', className)} {...props}>
        {Array.from({ length: 5 }).map((_, i) => (
          <Card key={i} padding="md">
            <CardContent className="py-4">
              <div className="flex items-center space-x-4">
                <Skeleton className="h-12 w-12 rounded" />
                <div className="flex-1 space-y-2">
                  <Skeleton className="h-4 w-3/4" />
                  <Skeleton className="h-3 w-1/2" />
                </div>
                <div className="flex space-x-2">
                  <Skeleton className="h-8 w-16" />
                  <Skeleton className="h-8 w-8" />
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    );
  }

  // ❌ État d'erreur
  if (error) {
    return (
      <Card className={cn('text-center py-8', className)} {...props}>
        <CardContent>
          <div className="text-red-500 mb-2">
            <svg
              className="h-12 w-12 mx-auto"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <Typography variant="large" className="text-gray-900 mb-2">
            Erreur de chargement
          </Typography>
          <Typography variant="muted">{error}</Typography>
        </CardContent>
      </Card>
    );
  }

  // 📭 État vide
  if (!tracks || tracks.length === 0) {
    return (
      <Card className={cn('text-center py-12', className)} {...props}>
        <CardContent>
          <div className="text-gray-400 mb-4">
            {emptyIcon || (
              <svg
                className="h-16 w-16 mx-auto"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={1}
                  d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                />
              </svg>
            )}
          </div>
          <Typography variant="large" className="text-gray-500">
            {emptyMessage}
          </Typography>
        </CardContent>
      </Card>
    );
  }

  // 🎵 Rendu compact
  if (variant === 'compact') {
    return (
      <div className={cn('space-y-1', className)} {...props}>
        {tracks.map((track, index) => (
          <div
            key={track.id}
            className="flex items-center justify-between rounded-lg py-2 px-3 transition-colors hover:bg-gray-50"
          >
            <div className="flex items-center space-x-3 flex-1 min-w-0">
              {/* 🔢 Numéro de piste */}
              <span className="text-sm text-gray-400 w-6 text-center">
                {index + 1}
              </span>

              {/* 📝 Informations de la piste */}
              <div className="flex-1 min-w-0">
                <p className="font-medium text-gray-900 truncate">
                  {track.name}
                </p>
                {showArtists && track.artists.length > 0 && (
                  <p className="text-sm text-gray-500 truncate">
                    {track.artists.map(artist => artist.name).join(', ')}
                  </p>
                )}
              </div>
            </div>

            {/* ⚡ Actions rapides */}
            {showActions && (
              <div className="flex items-center space-x-1">
                {onTrackFavorite && (
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={e => {
                      e.stopPropagation();
                      onTrackFavorite(track);
                    }}
                    className={cn(
                      'h-8 w-8 p-0',
                      favoriteTrackIds.includes(track.id)
                        ? 'text-red-500 hover:text-red-600'
                        : 'text-gray-400 hover:text-gray-600'
                    )}
                  >
                    <svg
                      className="h-4 w-4"
                      fill={
                        favoriteTrackIds.includes(track.id)
                          ? 'currentColor'
                          : 'none'
                      }
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                      />
                    </svg>
                  </Button>
                )}

                {onTrackPlay && (
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={e => {
                      e.stopPropagation();
                      onTrackPlay(track);
                    }}
                    className="h-8 w-8 p-0 text-spotify-green hover:text-green-600"
                  >
                    <svg
                      className="h-4 w-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h2a2 2 0 012 2v8a2 2 0 01-2 2H9a2 2 0 01-2-2V8a2 2 0 012-2z"
                      />
                    </svg>
                  </Button>
                )}
              </div>
            )}
          </div>
        ))}
      </div>
    );
  }

  // 🎵 Rendu détaillé ou par défaut
  return (
    <div className={cn('space-y-3', className)} {...props}>
      {tracks.map((track, index) => (
        <Card key={track.id} hover className="transition-all">
          <CardContent className="py-4">
            <div className="flex items-center space-x-4">
              {/* 🎨 Icône/numéro de piste */}
              <div className="flex-shrink-0">
                {variant === 'detailed' ? (
                  <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-spotify-green to-green-600 text-white">
                    <svg
                      className="h-6 w-6"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                      />
                    </svg>
                  </div>
                ) : (
                  <div className="flex h-10 w-10 items-center justify-center rounded bg-gray-100 text-gray-600">
                    <span className="text-sm font-medium">{index + 1}</span>
                  </div>
                )}
              </div>

              {/* 📝 Informations de la piste */}
              <div className="flex-1 min-w-0">
                <h3 className="font-semibold text-gray-900 truncate">
                  {track.name}
                </h3>

                {showArtists && track.artists.length > 0 && (
                  <div className="flex items-center space-x-1 mt-1">
                    <span className="text-sm text-gray-500">par</span>
                    <div className="flex items-center space-x-2">
                      {track.artists.map((artist, artistIndex) => (
                        <span key={artist.id} className="text-sm">
                          <Link
                            to={`/artists/${artist.id}`}
                            className="text-spotify-green hover:text-green-600 hover:underline"
                          >
                            {artist.name}
                          </Link>
                          {artistIndex < track.artists.length - 1 && (
                            <span className="text-gray-400 ml-1">,</span>
                          )}
                        </span>
                      ))}
                    </div>
                  </div>
                )}

                {variant === 'detailed' && (
                  <div className="mt-2 text-xs text-gray-500">
                    <span>ID: {track.id}</span>
                  </div>
                )}
              </div>

              {/* 🎯 Actions */}
              {showActions && (
                <div className="flex items-center space-x-2">
                  {onTrackFavorite && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={e => {
                        e.stopPropagation();
                        onTrackFavorite(track);
                      }}
                      className={cn(
                        favoriteTrackIds.includes(track.id)
                          ? 'text-red-500 hover:text-red-600'
                          : 'text-gray-400 hover:text-gray-600'
                      )}
                    >
                      <svg
                        className="h-4 w-4 mr-1"
                        fill={
                          favoriteTrackIds.includes(track.id)
                            ? 'currentColor'
                            : 'none'
                        }
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        />
                      </svg>
                      {favoriteTrackIds.includes(track.id)
                        ? 'Favoris'
                        : 'Ajouter'}
                    </Button>
                  )}

                  {onTrackPlay && (
                    <Button
                      variant="spotify"
                      size="sm"
                      onClick={e => {
                        e.stopPropagation();
                        onTrackPlay(track);
                      }}
                    >
                      <svg
                        className="h-4 w-4 mr-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h2a2 2 0 012 2v8a2 2 0 01-2 2H9a2 2 0 01-2-2V8a2 2 0 012-2z"
                        />
                      </svg>
                      Écouter
                    </Button>
                  )}
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  );
};
