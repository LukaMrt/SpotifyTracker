// 🎤 ARTIST CARD COMPONENT - Carte d'affichage d'un artiste
import type { HTMLAttributes } from 'react';
import { Link } from 'react-router-dom';

import type { Artist } from '@/api/spotifyApi';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  Button,
} from '@/components/ui';
import { cn } from '@/utils/cn';

// 🔗 Interface pour les props du ArtistCard
interface ArtistCardProps extends HTMLAttributes<HTMLDivElement> {
  artist: Artist;
  showActions?: boolean;
  variant?: 'default' | 'compact' | 'detailed';
  onView?: (artist: Artist) => void;
  onFavorite?: (artist: Artist) => void;
  isFavorite?: boolean;
}

// 🎤 Composant ArtistCard
export const ArtistCard = ({
  artist,
  showActions = true,
  variant = 'default',
  onView,
  onFavorite,
  isFavorite = false,
  className,
  ...props
}: ArtistCardProps) => {
  // 🎨 Styles conditionnels selon la variante
  const cardStyles = {
    default: 'h-full',
    compact: 'h-auto',
    detailed: 'h-full min-h-[200px]',
  };

  const handleView = () => {
    if (onView) {
      onView(artist);
    }
  };

  const handleFavorite = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (onFavorite) {
      onFavorite(artist);
    }
  };

  // 🎤 Rendu compact
  if (variant === 'compact') {
    return (
      <Card
        className={cn(
          'transition-all hover:shadow-md',
          cardStyles.compact,
          className
        )}
        hover
        {...props}
      >
        <CardContent className="flex items-center justify-between py-3">
          <div className="flex items-center space-x-3">
            {/* 👤 Avatar placeholder */}
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-spotify-green/10 text-spotify-green">
              <span className="text-sm font-semibold">
                {artist.name.charAt(0).toUpperCase()}
              </span>
            </div>

            {/* 📝 Nom de l'artiste */}
            <div>
              <h3 className="font-medium text-gray-900">{artist.name}</h3>
            </div>
          </div>

          {/* ⚡ Actions rapides */}
          {showActions && (
            <div className="flex items-center space-x-2">
              {onFavorite && (
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={handleFavorite}
                  className={cn(
                    'h-8 w-8 p-0',
                    isFavorite
                      ? 'text-red-500 hover:text-red-600'
                      : 'text-gray-400 hover:text-gray-600'
                  )}
                >
                  {/* ❤️ Icône cœur */}
                  <svg
                    className="h-4 w-4"
                    fill={isFavorite ? 'currentColor' : 'none'}
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

              <Link to={`/artists/${artist.id}`}>
                <Button variant="primary" size="sm">
                  Voir
                </Button>
              </Link>
            </div>
          )}
        </CardContent>
      </Card>
    );
  }

  // 🎤 Rendu détaillé ou par défaut
  return (
    <Card
      className={cn(
        'transition-all hover:shadow-md cursor-pointer',
        cardStyles[variant],
        className
      )}
      hover
      onClick={handleView}
      {...props}
    >
      <CardHeader className="text-center">
        {/* 👤 Avatar principal */}
        <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-spotify-green to-green-600 text-white shadow-lg">
          <span className="text-2xl font-bold">
            {artist.name.charAt(0).toUpperCase()}
          </span>
        </div>

        <CardTitle className="text-center">{artist.name}</CardTitle>

        {variant === 'detailed' && (
          <div className="text-sm text-gray-500">
            <p>ID: {artist.id}</p>
          </div>
        )}
      </CardHeader>

      <CardContent>
        {variant === 'detailed' && (
          <div className="space-y-3">
            <div className="rounded-lg bg-gray-50 p-3">
              <h4 className="text-sm font-medium text-gray-700 mb-2">
                Informations
              </h4>
              <div className="space-y-1 text-sm text-gray-600">
                <div className="flex justify-between">
                  <span>Nom:</span>
                  <span className="font-medium">{artist.name}</span>
                </div>
                <div className="flex justify-between">
                  <span>ID:</span>
                  <span className="font-mono text-xs">{artist.id}</span>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* 🎯 Actions */}
        {showActions && (
          <div className="flex items-center justify-between pt-4">
            <div className="flex space-x-2">
              {onFavorite && (
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={handleFavorite}
                  className={cn(
                    isFavorite
                      ? 'text-red-500 hover:text-red-600'
                      : 'text-gray-400 hover:text-gray-600'
                  )}
                >
                  {/* ❤️ Icône cœur */}
                  <svg
                    className="h-4 w-4 mr-1"
                    fill={isFavorite ? 'currentColor' : 'none'}
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
                  {isFavorite ? 'Favoris' : 'Ajouter'}
                </Button>
              )}
            </div>

            <Link to={`/artists/${artist.id}`}>
              <Button variant="primary" size="sm">
                Voir détails
              </Button>
            </Link>
          </div>
        )}
      </CardContent>
    </Card>
  );
};
