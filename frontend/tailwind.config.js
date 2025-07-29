/** @type {import('tailwindcss').Config} */
export default {
  // 📁 Fichiers à scanner pour les classes CSS
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],

  // 🎨 Thème personnalisé
  theme: {
    extend: {
      // 🎵 Couleurs personnalisées pour Spotify
      colors: {
        'spotify-green': '#1DB954',
        'spotify-black': '#191414',
        'spotify-gray': '#535353',
      },

      // 📱 Breakpoints personnalisés si nécessaire
      screens: {
        xs: '475px',
      },
    },
  },

  // 🔌 Plugins Tailwind
  plugins: [],
};
