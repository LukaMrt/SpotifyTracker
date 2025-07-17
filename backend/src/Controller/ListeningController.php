<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Repository\ListeningRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/listenings', name: 'api_listenings_')]
class ListeningController extends AbstractController
{
    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository
    ) {
    }

    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate || !$endDate) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $playlistId = $request->query->get('playlist_id');
            $artistId = $request->query->get('artist_id');
            $trackId = $request->query->get('track_id');

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
                    'playlist_id' => $playlistId,
                    'artist_id' => $artistId,
                    'track_id' => $trackId,
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while retrieving listenings: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/stats/artists', name: 'stats_artists', methods: [Request::METHOD_GET])]
    public function artistStats(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate || !$endDate) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $artistIds = $this->parseArrayParameter($request->query->get('artist_ids'));

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
                    'artist_ids' => $artistIds,
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while retrieving artist statistics: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/stats/tracks', name: 'stats_tracks', methods: [Request::METHOD_GET])]
    public function trackStats(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate || !$endDate) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $trackIds = $this->parseArrayParameter($request->query->get('track_ids'));

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
                    'track_ids' => $trackIds,
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while retrieving track statistics: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/stats/playlists', name: 'stats_playlists', methods: [Request::METHOD_GET])]
    public function playlistStats(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate || !$endDate) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $playlistIds = $this->parseArrayParameter($request->query->get('playlist_ids'));

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
                    'playlist_ids' => $playlistIds,
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while retrieving playlist statistics: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/stats/summary', name: 'stats_summary', methods: [Request::METHOD_GET])]
    public function statsSummary(Request $request): JsonResponse
    {
        try {
            $startDate = $this->parseDate($request->query->get('start_date'));
            $endDate = $this->parseDate($request->query->get('end_date'));

            if (!$startDate || !$endDate) {
                return $this->json([
                    'error' => 'Both start_date and end_date are required in YYYY-MM-DD format'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get top 10 for each category
            $artistStats = $this->listeningRepository->getArtistStats($startDate, $endDate);
            $trackStats = $this->listeningRepository->getTrackStats($startDate, $endDate);
            $playlistStats = $this->listeningRepository->getPlaylistStats($startDate, $endDate);

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

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while retrieving summary statistics: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function parseDate(?string $dateString): ?\DateTimeImmutable
    {
        if (!$dateString) {
            return null;
        }

        try {
            return new \DateTimeImmutable($dateString);
        } catch (\Exception) {
            return null;
        }
    }

    private function parseArrayParameter(?string $parameter): ?array
    {
        if (!$parameter) {
            return null;
        }

        // Support both comma-separated values and JSON arrays
        if (str_starts_with(trim($parameter), '[')) {
            $decoded = json_decode($parameter, true);
            return is_array($decoded) ? $decoded : null;
        }

        return array_filter(array_map('trim', explode(',', $parameter)));
    }

    private function serializeListening($listening): array
    {
        $artists = array_map(function ($artist) {
            return [
                'id' => $artist->getId()->id,
                'name' => $artist->getName(),
            ];
        }, $listening->getTrack()->getArtists());

        $playlist = null;
        if ($listening->getPlaylist()) {
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
