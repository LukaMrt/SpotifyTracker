// 🎵 PAGE DES TRACKS

import { useGetTracksQuery } from '@/api/spotifyApi';
import { TrackList } from '@/components/business';
import { Heading1, Breadcrumb, BreadcrumbItem } from '@/components/ui';

export const TracksPage = () => {
  // 🎣 Même principe que pour les artistes !
  const { data: tracks, isLoading, error } = useGetTracksQuery();

  const handleTrackPlay = (track: any) => {
    // TODO: Implémenter la logique de lecture
    console.log('Playing track:', track.name);
  };

  const handleTrackFavorite = (track: any) => {
    // TODO: Implémenter la logique de favoris
    console.log('Toggle favorite for track:', track.name);
  };

  return (
    <div className="container mx-auto px-4 py-8">
      {/* 🧭 Navigation avec Breadcrumb */}
      <Breadcrumb className="mb-6">
        <BreadcrumbItem href="/">Accueil</BreadcrumbItem>
        <BreadcrumbItem isLast>Tracks</BreadcrumbItem>
      </Breadcrumb>

      <Heading1 className="mb-6">🎵 Tracks</Heading1>

      {/* 📊 Liste des tracks avec TrackList component */}
      <TrackList
        tracks={tracks || []}
        loading={isLoading}
        error={error ? 'Impossible de charger les tracks.' : ''}
        variant="default"
        showArtists={true}
        showActions={true}
        emptyMessage="Aucun track trouvé."
        onTrackPlay={handleTrackPlay}
        onTrackFavorite={handleTrackFavorite}
        favoriteTrackIds={[]} // TODO: Récupérer les favoris depuis le state
      />
    </div>
  );
};
