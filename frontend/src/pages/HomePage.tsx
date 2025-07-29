// 🏠 PAGE D'ACCUEIL
import { Link } from 'react-router-dom';

import {
  Heading1,
  Lead,
  Button,
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui';

export const HomePage = () => {
  return (
    <div className="container mx-auto px-4 py-8">
      {/* 🎯 Hero Section */}
      <div className="text-center mb-12">
        <Heading1 className="mb-4">🎵 Spotify Tracker</Heading1>
        <Lead className="mb-8">Suivez vos artistes et tracks préférés</Lead>

        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          {/* 🔗 Navigation vers les sections principales */}
          <Button variant="primary" size="lg" asChild>
            <Link to="/artists">🎤 Voir les Artistes</Link>
          </Button>

          <Button variant="spotify" size="lg" asChild>
            <Link to="/tracks">🎵 Voir les Tracks</Link>
          </Button>
        </div>
      </div>

      {/* 📋 Features Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-12">
        <Card hover>
          <CardHeader>
            <div className="text-4xl mb-2">🎤</div>
            <CardTitle>Artistes</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">
              Découvrez et suivez vos artistes préférés
            </p>
            <Button variant="ghost" size="sm" asChild>
              <Link to="/artists">Explorer →</Link>
            </Button>
          </CardContent>
        </Card>

        <Card hover>
          <CardHeader>
            <div className="text-4xl mb-2">🎵</div>
            <CardTitle>Tracks</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">
              Parcourez votre collection de morceaux
            </p>
            <Button variant="ghost" size="sm" asChild>
              <Link to="/tracks">Écouter →</Link>
            </Button>
          </CardContent>
        </Card>

        <Card hover>
          <CardHeader>
            <div className="text-4xl mb-2">📊</div>
            <CardTitle>Statistiques</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">
              Analysez vos habitudes d'écoute
            </p>
            <Button variant="ghost" size="sm" disabled>
              Bientôt disponible
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};
