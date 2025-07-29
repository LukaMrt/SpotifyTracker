// 🎤 PAGE DES ARTISTES
// Exemple parfait d'utilisation de RTK Query !

import { Link } from 'react-router-dom';

import { useGetArtistsQuery } from '@/api/spotifyApi';
import { ArtistCard } from '@/components/business';
import {
  Heading1,
  Card,
  CardContent,
  Spinner,
  Button,
  Breadcrumb,
  BreadcrumbItem,
} from '@/components/ui';

export const ArtistsPage = () => {
  // 🎣 HOOK RTK QUERY MAGIQUE !
  // Ce hook fait automatiquement la requête GET /artists
  const {
    data: artists, // 📦 Les données récupérées
    isLoading, // ⏳ True pendant le chargement
    error, // ❌ Erreur s'il y en a une
  } = useGetArtistsQuery();

  return (
    <div className="container mx-auto px-4 py-8">
      {/* 🧭 Navigation avec Breadcrumb */}
      <Breadcrumb className="mb-6">
        <BreadcrumbItem href="/">Accueil</BreadcrumbItem>
        <BreadcrumbItem isLast>Artistes</BreadcrumbItem>
      </Breadcrumb>

      <Heading1 className="mb-6">🎤 Artistes</Heading1>

      {/* ⏳ ÉTAT DE CHARGEMENT */}
      {isLoading && (
        <div className="flex flex-col items-center justify-center py-12">
          <Spinner size="lg" color="primary" />
          <p className="mt-4 text-gray-600">Chargement des artistes...</p>
        </div>
      )}

      {/* ❌ GESTION D'ERREUR */}
      {error && (
        <Card variant="outlined" className="mb-6 border-red-200 bg-red-50">
          <CardContent className="py-4">
            <div className="flex items-start space-x-3">
              <div className="text-red-500">
                <svg
                  className="h-5 w-5"
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
              <div>
                <p className="font-medium text-red-800">
                  Erreur : Impossible de charger les artistes.
                </p>
                <p className="text-sm text-red-600 mt-1">
                  Vérifiez que votre API backend est démarrée !
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* 📊 AFFICHAGE DES DONNÉES */}
      {artists && artists.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {artists.map(artist => (
            <ArtistCard
              key={artist.id}
              artist={artist}
              variant="default"
              showActions={true}
              onView={artist => {
                // Navigation déjà gérée par le Link dans le composant
                console.log('Viewing artist:', artist.name);
              }}
              onFavorite={artist => {
                // TODO: Implémenter la logique de favoris
                console.log('Toggle favorite for:', artist.name);
              }}
            />
          ))}
        </div>
      )}

      {/* 📝 MESSAGE SI AUCUN ARTISTE */}
      {artists && artists.length === 0 && (
        <Card className="text-center py-12">
          <CardContent>
            <div className="text-gray-400 mb-4">
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
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              Aucun artiste trouvé
            </h3>
            <p className="text-gray-500 mb-4">
              Ajoutez des données dans votre API backend !
            </p>
            <Button variant="primary" asChild>
              <Link to="/">Retour à l'accueil</Link>
            </Button>
          </CardContent>
        </Card>
      )}
    </div>
  );
};
