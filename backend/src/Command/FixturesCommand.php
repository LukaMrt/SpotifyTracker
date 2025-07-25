<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\Playlist;
use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Entity\Track;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures',
    description: 'Load listening fixtures with artists, tracks, playlists and listenings',
)]
class FixturesCommand
{
    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(
            description: 'Number of listenings to generate. Default is 100.',
            name: 'count',
            shortcut: 'c',
            suggestedValues: ['100', '500', '1000', '5000', '10000'],
        )]
        int $count = 100,
    ): int {
        $io->title('Loading Spotify Listening Fixtures');

        // Generate artists
        $io->section('Generating Artists');
        
        $artists = $this->generateArtists();
        $io->success(sprintf('Generated %d artists', count($artists)));

        // Generate playlists
        $io->section('Generating Playlists');
        
        $playlists = $this->generatePlaylists();
        $io->success(sprintf('Generated %d playlists', count($playlists)));

        // Generate tracks
        $io->section('Generating Tracks');
        
        $tracks = $this->generateTracks($artists);
        $io->success(sprintf('Generated %d tracks', count($tracks)));

        // Generate listenings  
        $io->section('Generating Listenings');
        
        // Clear any existing data first
        $this->listeningRepository->clearAll();
        
        $this->generateListenings($tracks, $playlists, $count);
        $io->success(sprintf('Generated %d listenings', $count));

        $io->success('All fixtures loaded successfully!');

        return Command::SUCCESS;
    }

    /**
     * @return Artist[]
     */
    private function generateArtists(): array
    {
        $artistsData = [
            ['id' => '0WqprkdDf9jCcdViOJGn68', 'name' => 'Carston'],
            ['id' => '2vOzV5WdvOYH3K1NJyt7wb', 'name' => 'Horizon Blue'],
            ['id' => '0bmxU94V20pNJ2Vie9kFYv', 'name' => 'Rea Garvey'],
            ['id' => '7jLSEPYCYQ5ssWU3BICqrW', 'name' => 'Picture This'],
            ['id' => '1ZrBGJWLL8NiAjgNifCy90', 'name' => 'Smith & Thell'],
            ['id' => '2b6gc4EVpO6OTlDvKrK852', 'name' => 'Lucky Chops'],
            ['id' => '2oX42qP5ineK3hrhBECLmj', 'name' => 'Andy Grammer'],
            ['id' => '0p3tzEAt0XWrBqbrwBoN1I', 'name' => 'Kyle Hume'],
            ['id' => '4DzXj2bn9ivDSjvJVSKL4x', 'name' => 'Luca Testa'],
            ['id' => '297i2RtGGQqofyuggOlE2l', 'name' => 'RYU & DANTE'],
            ['id' => '6LMPsablHHtwkl0myzVCgW', 'name' => 'Andrea Toscano'],
            ['id' => '1TPxbrTvRteumxJJMjg302', 'name' => 'Wanda Shakes'],
            ['id' => '4F6nLjcaBED8qUel8bBx6C', 'name' => 'Jax Jones'],
            ['id' => '5KKpBU5eC2tJDzf0wmlRp2', 'name' => 'RAYE'],
            ['id' => '3WrFJ7ztbogyGnTHbHJFl2', 'name' => 'The Beatles'],
        ];

        $artists = [];
        foreach ($artistsData as $data) {
            $artists[] = new Artist(
                new SpotifyId($data['id']),
                $data['name']
            );
        }

        return $artists;
    }

    /**
     * @return Playlist[]
     */
    private function generatePlaylists(): array
    {
        $playlistsData = [
            ['id' => '0068lzo1xXa9ED8ThypHU1', 'name' => 'Got You On My Mind'],
            ['id' => '00CohVTIAro42OEyH1crVW', 'name' => 'Somewhere Close To Heaven'],
            ['id' => '00Gbi2ytn6ZmA1ObVcPT93', 'name' => "Pixie's Parasol"],
            ['id' => '00HNi1t5MKe1UgQYkBd2BS', 'name' => 'Best Things'],
            ['id' => '00V8KJuZ0a089fa25Lc9t7', 'name' => 'Fine By Me'],
            ['id' => '00Xs2BB5Akxl0p0JPwlEDg', 'name' => 'Shoe Fits'],
            ['id' => '00aWZjvNgFOUcs9FaAzcVy', 'name' => 'More Than You Know - Hardstyle Remix'],
            ['id' => '00i7NDZA5Yp8i1aBC5zvPI', 'name' => 'We Just Gotta (Get Together)'],
            ['id' => '00lNx0OcTJrS3MKHcB80HY', 'name' => "You Don't Know Me - Radio Edit"],
            ['id' => '00vY0JBNlplMF3fg4aJgRM', 'name' => 'frame it'],
            ['id' => '00wCGmXuBzQCefa6khtSmp', 'name' => 'Danza 2020 - Live'],
            ['id' => '0110a0AM2nOV8yYa9u6kjQ', 'name' => 'Love Me Like a Friend'],
            ['id' => '014nzsOiXkV39ivgWROKIk', 'name' => 'Lost in Love'],
            ['id' => '016dLlAVQIkvND7FPAiitb', 'name' => 'Feel Like Clarity'],
            ['id' => '01PuE6Rd816kRrPXcr2EyP', 'name' => 'Home to You'],
            ['id' => '01RJQlTi0aR0syDcSFLzTv', 'name' => 'Lego Blocks'],
            ['id' => '01S9Y2qZZkQs23TJmTmmUb', 'name' => 'Ruin'],
            ['id' => '01SfTM5nfCou5gQL70r6gs', 'name' => 'Golden Slumbers - Remastered 2009'],
            ['id' => '01VJuIGhHGx1U5dCXdXzi4', 'name' => 'Edge Of The Earth'],
            ['id' => '01iyCAUm8EvOFqVWYJ3dVX', 'name' => 'Dancing Queen'],
            ['id' => '02MWAaffLxlfxAUY7c5dvx', 'name' => 'Heat Waves'],
            ['id' => '02Nyn3a2nHssi9rNg3jkdL', 'name' => 'Break Through'],
            ['id' => '02bJ6uGeHKfNOhIc9qyA8e', 'name' => 'Wild Child'],
            ['id' => '02itaCXOdC54J0ISjqqFAp', 'name' => 'All Around The World (La La La)'],
            ['id' => '02jCzgsSElP69X2i4Jv1ix', 'name' => 'The Hardest Love'],
            ['id' => '02lQKzdCsP0YeRDRjJunz7', 'name' => 'Galu'],
            ['id' => '02ssEFV2TFLeNo5KZivt0W', 'name' => 'Meadows'],
            ['id' => '030RDC2ayPOUM32F9IH7eE', 'name' => 'When the Rain Begins to Fall'],
            ['id' => '031aONFGxa489W6C0AOdSd', 'name' => 'Havana'],
            ['id' => '0389rb3gBABypNRsy05e03', 'name' => 'CRASH & BURN'],
            ['id' => '03AAJOztWCV58AMEvtOc3i', 'name' => 'loading'],
            ['id' => '03Cpo8eXUd12k8TXDAtExs', 'name' => 'Nothing But Love'],
            ['id' => '03LH899Fyk7Fwt1kZsGdM2', 'name' => 'Blank Me'],
            ['id' => '03S5dBXGXyS8S9fyLNRS2P', 'name' => 'Loved Somebody Else'],
            ['id' => '03T4ttRCiLXST6MZjeMwmR', 'name' => 'Orphans'],
            ['id' => '03UrZgTINDqvnUMbbIMhql', 'name' => 'Gangnam Style (강남스타일)'],
        ];

        $playlists = [];
        foreach ($playlistsData as $data) {
            $playlists[] = new Playlist(
                new SpotifyId($data['id']),
                $data['name']
            );
        }

        return $playlists;
    }

    /**
     * @param Artist[] $artists
     * @return Track[]
     */
    private function generateTracks(array $artists): array
    {
        $tracksData = [
            ['id' => '4iV5W9uYEdYUVa79Axb7Rh', 'name' => 'Anti-Hero', 'artist_indices' => [1]],
            ['id' => '1McMsnEElThX1knmY9oliG', 'name' => 'As It Was', 'artist_indices' => [10]],
            ['id' => '7qiZfU4dY1lWllzX7mPBI3', 'name' => 'Shape of You', 'artist_indices' => [10]],
            ['id' => '6dOtVTDdiauQNBQEDOtlAB', 'name' => 'Blinding Lights', 'artist_indices' => [13]],
            ['id' => '7MXVkk9YMctZqd1Srtv4MB', 'name' => 'Starboy', 'artist_indices' => [13, 4]],
            ['id' => '0VjIjW4GlUZAMYd2vXMi3b', 'name' => 'Blinded by the Light', 'artist_indices' => [13]],
            ['id' => '4VqPOruhp5EdPBeR92t6lQ', 'name' => 'Uptown Funk', 'artist_indices' => [5]],
            ['id' => '32OlwWuMpZ6b0aN2RZOeMS', 'name' => 'Someone Like You', 'artist_indices' => [11]],
            ['id' => '11dFghVXANMlKmJXsNCbNl', 'name' => 'Here Comes the Sun', 'artist_indices' => [12]],
            ['id' => '6RUKPb4LETWmmr3iAEQktW', 'name' => 'Bohemian Rhapsody', 'artist_indices' => [3]],
            ['id' => '4u7EnebtmKWzUH433cf5Qv', 'name' => "Don't Stop Me Now", 'artist_indices' => [3]],
            ['id' => '39shCrLaLEUFZvXtNAu8zF', 'name' => 'We Will Rock You', 'artist_indices' => [3]],
            ['id' => '3AJwUDP919kvQ9QcozQPxg', 'name' => 'Lose Yourself', 'artist_indices' => [9]],
            ['id' => '561jH07mF1jHuk7KlaeF0s', 'name' => 'The Real Slim Shady', 'artist_indices' => [9]],
            ['id' => '7lQ8MOhq6IN2w8EYcFNSUk', 'name' => 'Without Me', 'artist_indices' => [9]],
            ['id' => '3n3Ppam7vgaVa1iaRUc9Lp', 'name' => 'Mr. Brightside', 'artist_indices' => [14]],
            ['id' => '5CQ30WqJwcep0pYcV4AMNc', 'name' => 'Sunflower', 'artist_indices' => [7, 14]],
            ['id' => '0tgVpDi06FyKpA1z0VMD4v', 'name' => 'Perfect', 'artist_indices' => [10]],
            ['id' => '4c5evO8mtMSNYylVmGLdba', 'name' => 'get him back!', 'artist_indices' => [6]],
            ['id' => '0yLdNVWF3Srea0uzk55zFn', 'name' => 'good 4 u', 'artist_indices' => [6]],
            ['id' => '5wANPM4fQCJwkGd4rN57mH', 'name' => 'vampire', 'artist_indices' => [6]],
            ['id' => '76cy1WJvNGJTj78UqeA5zr', 'name' => 'traitor', 'artist_indices' => [6]],
            ['id' => '4Dvkj6JhhA12EX05fT7y2e', 'name' => 'One More Time', 'artist_indices' => [4]],
            ['id' => '0DiWol3AO6WpXZgp0goxAV', 'name' => 'Get Lucky', 'artist_indices' => [4]],
            ['id' => '6QSpuOTqJsmuDPnLih1ILq', 'name' => 'Instant Crush', 'artist_indices' => [4]],
            ['id' => '4Zb4pGNTALJ0evn6qlXOCh', 'name' => 'Harder Better Faster Stronger', 'artist_indices' => [4]],
            ['id' => '1je1IMUlBXcx1P8zbZcyFE', 'name' => "God's Plan", 'artist_indices' => [2]],
            ['id' => '7KXjTSCq5nL1LoYtL7XAwS', 'name' => 'Hotline Bling', 'artist_indices' => [2]],
            ['id' => '0wwPcA6wtMf6HUMpIRdeP7', 'name' => 'One Dance', 'artist_indices' => [2]],
            ['id' => '7fzVpLZbgBV1R3Y8LePRRm', 'name' => 'In My Feelings', 'artist_indices' => [2]],
            ['id' => '0hVXuCcriWRGvwMV1r5Yn9', 'name' => 'Tum Hi Ho', 'artist_indices' => [0]],
            ['id' => '4N4S8bUhKRSl1fJLJdM3Gp', 'name' => 'Tera Hone Laga Hoon', 'artist_indices' => [0]],
            ['id' => '6PGoSes0D9eUDeeAafB2As', 'name' => 'Channa Mereya', 'artist_indices' => [0]],
            ['id' => '4xkOaSrkexMciUUogZKVTS', 'name' => 'Raabta', 'artist_indices' => [0]],
            ['id' => '11LmqTE2naFULdEP94AUBa', 'name' => 'Circles', 'artist_indices' => [7]],
            ['id' => '4xqrdfXkTW4T0RauPLv3WA', 'name' => 'rockstar', 'artist_indices' => [7]],
        ];

        $tracks = [];
        foreach ($tracksData as $data) {
            $trackArtists = [];
            foreach ($data['artist_indices'] as $index) {
                $trackArtists[] = $artists[$index];
            }

            $tracks[] = new Track(
                new SpotifyId($data['id']),
                $data['name'],
                $trackArtists
            );
        }

        return $tracks;
    }

    /**
     * @param Track[] $tracks
     * @param Playlist[] $playlists
     * @throws \DateMalformedStringException
     */
    private function generateListenings(array $tracks, array $playlists, int $count): void
    {
        $now = new \DateTimeImmutable();
        $oneMonthAgo = $now->modify('-1 month');
        $yesterday = $now->modify('-1 day');
        
        // Calculate total minutes in the range
        $totalMinutes = (int) $yesterday->diff($oneMonthAgo)->format('%a') * 24 * 60;
        
        // Predefined patterns for deterministic data
        $trackPattern = $this->getTrackPlaybackPattern($tracks);
        $playlistPattern = $this->getPlaylistPattern($playlists);

        for ($i = 0; $i < $count; ++$i) {
            // Deterministic time calculation - spread evenly across the month
            $minuteOffset = (int) (($i / $count) * $totalMinutes);
            $listeningTime = $oneMonthAgo->modify(sprintf('+%d minutes', $minuteOffset));

            // Deterministic track selection
            $trackIndex = $i % count($trackPattern);
            $track = $trackPattern[$trackIndex];

            // Deterministic playlist selection (80% with playlist, 20% without)
            $playlist = ($i % 5) === 4 ? null : $playlistPattern[$i % count($playlistPattern)];

            $listening = new Listening(
                $listeningTime,
                $track,
                $playlist
            );

            $this->listeningRepository->save($listening);
        }
    }

    /**
     * @param Track[] $tracks
     * @return Track[]
     */
    private function getTrackPlaybackPattern(array $tracks): array
    {
        $pattern = [];
        
        // Popular tracks (first 10) appear more frequently
        for ($i = 0; $i < 10 && $i < count($tracks); ++$i) {
            // Add popular tracks 5 times each
            for ($j = 0; $j < 5; ++$j) {
                $pattern[] = $tracks[$i];
            }
        }
        
        // Moderately popular tracks (next 10) appear less frequently
        for ($i = 10; $i < 20 && $i < count($tracks); ++$i) {
            // Add moderately popular tracks 3 times each
            for ($j = 0; $j < 3; ++$j) {
                $pattern[] = $tracks[$i];
            }
        }
        
        // Remaining tracks appear once each
        $counter = count($tracks);
        
        // Remaining tracks appear once each
        for ($i = 20; $i < $counter; ++$i) {
            $pattern[] = $tracks[$i];
        }
        
        return $pattern;
    }

    /**
     * @param Playlist[] $playlists
     * @return Playlist[]
     */
    private function getPlaylistPattern(array $playlists): array
    {
        // Return first 20 playlists in a repeating pattern
        return array_slice($playlists, 0, min(20, count($playlists)));
    }

}
