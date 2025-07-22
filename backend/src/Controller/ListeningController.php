<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListeningController extends AbstractController
{
    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository
    ) {
    }

    #[Route('/api/listenings', name: 'api_listenings_list', methods: [Request::METHOD_GET])]
    public function list(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate instanceof \DateTimeImmutable || !$endDate instanceof \DateTimeImmutable) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $playlistIdParam = $request->query->get('playlist_id');
            $playlistId = $playlistIdParam !== null ? new SpotifyId($playlistIdParam) : null;

            $artistIdParam = $request->query->get('artist_id');
            $artistId = $artistIdParam !== null ? new SpotifyId($artistIdParam) : null;

            $trackIdParam = $request->query->get('track_id');
            $trackId = $trackIdParam !== null ? new SpotifyId($trackIdParam) : null;

            $listenings = $this->listeningRepository->findByDateRange(
                $startDate,
                $endDate,
                $playlistId,
                $artistId,
                $trackId
            );

            return $this->json([
                'data' => array_map([$this, 'serializeListening'], $listenings),
                'count' => count($listenings),
                'filters' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'playlist_id' => $playlistId?->id,
                    'artist_id' => $artistId?->id,
                    'track_id' => $trackId?->id,
                ]
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'An error occurred while retrieving listenings: ' . $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/listenings/stats/artists', name: 'api_listenings_stats_artists', methods: [Request::METHOD_GET])]
    public function artistStats(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate instanceof \DateTimeImmutable || !$endDate instanceof \DateTimeImmutable) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $artistIds = $this->parseSpotifyIdArrayParameter($request->query->get('artist_ids'));

            $stats = $this->listeningRepository->getArtistStats(
                $startDate,
                $endDate,
                $artistIds
            );

            return $this->json([
                'data' => $stats,
                'total_artists' => count($stats),
                'total_listenings' => array_sum($stats),
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'filters' => [
                    'artist_ids' => array_map(fn(SpotifyId $id): string => $id->id, $artistIds),
                ]
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'An error occurred while retrieving artist statistics: ' . $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/listenings/stats/tracks', name: 'api_listenings_stats_tracks', methods: [Request::METHOD_GET])]
    public function trackStats(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate instanceof \DateTimeImmutable || !$endDate instanceof \DateTimeImmutable) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $trackIds = $this->parseSpotifyIdArrayParameter($request->query->get('track_ids'));

            $stats = $this->listeningRepository->getTrackStats(
                $startDate,
                $endDate,
                $trackIds
            );

            return $this->json([
                'data' => $stats,
                'total_tracks' => count($stats),
                'total_listenings' => array_sum($stats),
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'filters' => [
                    'track_ids' => array_map(fn(SpotifyId $id): string => $id->id, $trackIds),
                ]
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'An error occurred while retrieving track statistics: ' . $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/listenings/stats/playlists', name: 'api_listenings_stats_playlists', methods: [Request::METHOD_GET])]
    public function playlistStats(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate instanceof \DateTimeImmutable || !$endDate instanceof \DateTimeImmutable) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $playlistIds = $this->parseSpotifyIdArrayParameter($request->query->get('playlist_ids'));

            $stats = $this->listeningRepository->getPlaylistStats(
                $startDate,
                $endDate,
                $playlistIds
            );

            return $this->json([
                'data' => $stats,
                'total_playlists' => count($stats),
                'total_listenings' => array_sum($stats),
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'filters' => [
                    'playlist_ids' => array_map(fn(SpotifyId $id): string => $id->id, $playlistIds),
                ]
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'An error occurred while retrieving playlist statistics: ' . $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/listenings/stats/summary', name: 'api_listenings_stats_summary', methods: [Request::METHOD_GET])]
    public function statsSummary(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate instanceof \DateTimeImmutable || !$endDate instanceof \DateTimeImmutable) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get top 10 for each category
            $artistStats = $this->listeningRepository->getArtistStats($startDate, $endDate, []);
            $trackStats = $this->listeningRepository->getTrackStats($startDate, $endDate, []);
            $playlistStats = $this->listeningRepository->getPlaylistStats($startDate, $endDate, []);

            // Limit to top 10 for summary
            $topArtists = array_slice($artistStats, 0, 10, true);
            $topTracks = array_slice($trackStats, 0, 10, true);
            $topPlaylists = array_slice($playlistStats, 0, 10, true);

            return $this->json([
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'summary' => [
                    'total_listenings' => array_sum($artistStats), // Total from artists (most accurate)
                    'unique_artists' => count($artistStats),
                    'unique_tracks' => count($trackStats),
                    'unique_playlists' => count($playlistStats),
                ],
                'top_artists' => $topArtists,
                'top_tracks' => $topTracks,
                'top_playlists' => $topPlaylists,
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'An error occurred while retrieving summary statistics: ' . $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function parseDate(?string $dateString): ?\DateTimeImmutable
    {
        if ($dateString === null) {
            return null;
        }

        try {
            return new \DateTimeImmutable($dateString);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return string[]
     */
    private function parseArrayParameter(?string $parameter): array
    {
        if ($parameter === null) {
            return [];
        }

        // Support both comma-separated values and JSON arrays
        if (str_starts_with(trim($parameter), '[')) {
            $decoded = json_decode($parameter, true);
            if (!is_array($decoded)) {
                return [];
            }
            
            // Ensure we return an array of strings
            return array_filter($decoded, fn($value): bool => is_string($value));
        }

        return array_filter(array_map('trim', explode(',', $parameter)), fn(string $value): bool => $value !== '');
    }

    /**
     * @return SpotifyId[]
     */
    private function parseSpotifyIdArrayParameter(?string $parameter): array
    {
        $stringIds = $this->parseArrayParameter($parameter);
        return array_map(fn(string $id): SpotifyId => new SpotifyId($id), $stringIds);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeListening(Listening $listening): array
    {
        $artists = array_map(fn($artist): array => [
            'id' => $artist->getId()->id,
            'name' => $artist->getName(),
        ], $listening->getTrack()->getArtists());

        $playlist = null;
        if ($listening->getPlaylist() instanceof \App\Domain\Spotify\Entity\Playlist) {
            $playlist = [
                'id' => $listening->getPlaylist()->getId()->id,
                'name' => $listening->getPlaylist()->getName(),
            ];
        }

        return [
            'date_time' => $listening->getDateTime()->format('Y-m-d H:i:s'),
            'track' => [
                'id' => $listening->getTrack()->getId()->id,
                'name' => $listening->getTrack()->getName(),
                'artists' => $artists,
            ],
            'playlist' => $playlist,
        ];
    }
}
