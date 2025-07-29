// 👤 PAGE DE DÉTAIL D'UN ARTISTE

import { Link, useParams } from 'react-router-dom';

import { useGetArtistQuery } from '@/api/spotifyApi';

export const ArtistDetailPage = () => {
  // 🔍 RÉCUPÉRATION DE L'ID DEPUIS L'URL
  // Si l'URL est /artists/123, alors params.id = "123"
  const { id } = useParams<{ id: string }>();

  // 🎣 HOOK AVEC PARAMÈTRE
  // On passe l'ID pour faire GET /artists/123
  const { data: artist, isLoading, error } = useGetArtistQuery(id!); // Le ! dit à TypeScript que id n'est jamais undefined

  return (
    <div className="container mx-auto px-4 py-8">
      {/* 🧭 Navigation */}
      <div className="mb-6">
        <Link to="/artists" className="text-blue-500 hover:text-blue-600">
          ← Retour aux artistes
        </Link>
      </div>

      {/* ⏳ Chargement */}
      {isLoading && (
        <div className="text-center py-8">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          <p className="mt-2 text-gray-600">Chargement de l'artiste...</p>
        </div>
      )}

      {/* ❌ Erreur */}
      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          <strong>Erreur :</strong> Artiste non trouvé (ID: {id})
        </div>
      )}

      {/* 👤 Détails de l'artiste */}
      {artist && (
        <div className="bg-white rounded-lg shadow-lg p-8">
          <div className="text-center mb-8">
            <h1 className="text-4xl font-bold text-gray-900 mb-4">
              🎤 {artist.name}
            </h1>

            <div className="space-y-2 text-gray-600">
              <p>
                <strong>ID interne:</strong> {artist.id}
              </p>
              <p>
                <strong>Spotify ID:</strong> {artist.spotifyId}
              </p>
            </div>
          </div>

          {/* 🖼️ Placeholder pour image (si disponible) */}
          <div className="text-center">
            <div className="w-64 h-64 mx-auto bg-gray-200 rounded-full flex items-center justify-center text-6xl mb-6">
              🎤
            </div>
            <p className="text-gray-500 text-sm">
              Image à venir quand l'API aura des URLs d'images
            </p>
          </div>
        </div>
      )}
    </div>
  );
};
