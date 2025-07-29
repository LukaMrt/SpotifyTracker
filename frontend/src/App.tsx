// 🧭 CONFIGURATION DU ROUTER PRINCIPAL
import { BrowserRouter, Routes, Route } from 'react-router-dom';

import { HomePage, ArtistsPage, ArtistDetailPage, TracksPage } from '@/pages';

function App() {
  return (
    // 🌐 BROWSER ROUTER : Active le routing dans l'app
    <BrowserRouter>
      <div className="min-h-screen bg-gray-50">
        {/* 🗺️ DÉFINITION DES ROUTES */}
        <Routes>
          {/* 🏠 Route d'accueil */}
          <Route path="/" element={<HomePage />} />

          {/* 🎤 Routes des artistes */}
          <Route path="/artists" element={<ArtistsPage />} />
          <Route path="/artists/:id" element={<ArtistDetailPage />} />

          {/* 🎵 Route des tracks */}
          <Route path="/tracks" element={<TracksPage />} />

          {/* 🚫 Route 404 (optionnelle) */}
          <Route
            path="*"
            element={
              <div className="container mx-auto px-4 py-8 text-center">
                <h1 className="text-2xl font-bold text-gray-900 mb-4">
                  404 - Page non trouvée
                </h1>
                <a href="/" className="text-blue-500 hover:text-blue-600">
                  Retour à l'accueil
                </a>
              </div>
            }
          />
        </Routes>
      </div>
    </BrowserRouter>
  );
}

export default App;
