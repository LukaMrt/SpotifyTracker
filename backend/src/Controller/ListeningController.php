<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use App\Infrastructure\Repository\ListeningMysqlRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Listenings', description: 'Operations related to music listening data')]
class ListeningController extends AbstractController
{
    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository
    ) {
    }

    #[Route('/api/listenings', name: 'api_listeningsapi_listenings_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/listenings',
        description: 'Get listening records filtered by date range and optionally by playlist, artist, or track',
        summary: 'Retrieve listenings between two dates'
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'Start date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-01')
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'End date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-31')
    )]
    #[OA\Parameter(
        name: 'playlist_id',
        description: 'Filter by specific playlist ID',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'abc123def456')
    )]
    #[OA\Parameter(
        name: 'artist_id',
        description: 'Filter by specific artist ID',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'artist123')
    )]
    #[OA\Parameter(
        name: 'track_id',
        description: 'Filter by specific track ID',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'track456')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response with listening data',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'count', type: 'integer', example: 42),
            ]
        )
    )]
    public function list(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?SpotifyId $playlistId = null,
        ?SpotifyId $artistId = null,
        ?SpotifyId $trackId = null,
    ): JsonResponse {
        $listenings = $this->listeningRepository->findByDateRange(
            $startDate,
            $endDate,
            $playlistId,
            $artistId,
            $trackId
        );

        return $this->json(['count' => count($listenings)]);
    }

    /**
     * @param SpotifyId[] $artistIds
     */
    #[Route('/api/listenings/stats/artists', name: 'api_listeningsapi_listenings_stats_artists', methods: ['GET'])]
    #[OA\Get(
        path: '/api/listenings/stats/artists',
        description: 'Retrieve listening statistics for artists within a date range, optionally filtered by specific artist IDs',
        summary: 'Get artist listening statistics'
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'Start date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-01')
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'End date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-31')
    )]
    #[OA\Parameter(
        name: 'artist_ids',
        description: 'Comma-separated list of artist IDs to filter by (optional)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'artist1,artist2,artist3')
    )]
    #[OA\Response(
        response: 200,
        description: 'Artist statistics',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'object', example: ['Artist Name' => 42, 'Another Artist' => 25], additionalProperties: new OA\AdditionalProperties(type: 'integer')),
                new OA\Property(property: 'total_artists', type: 'integer', example: 10),
                new OA\Property(property: 'total_listenings', type: 'integer', example: 150),
                new OA\Property(property: 'period', properties: [
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date')
                ], type: 'object'),
                new OA\Property(property: 'filters', properties: [
                    new OA\Property(property: 'artist_ids', type: 'array', items: new OA\Items(type: 'string'))
                ], type: 'object')
            ]
        )
    )]
    public function artistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $artistIds = []
    ): JsonResponse {
        $stats = $this->listeningRepository->getArtistStats(
            $startDate,
            $endDate,
            $artistIds
        );

        return $this->json(['data' => $stats]);
    }

    /**
     * @param SpotifyId[] $trackIds
     */
    #[Route('/api/listenings/stats/tracks', name: 'api_listeningsapi_listenings_stats_tracks', methods: ['GET'])]
    #[OA\Get(
        path: '/api/listenings/stats/tracks',
        description: 'Retrieve listening statistics for tracks within a date range, optionally filtered by specific track IDs',
        summary: 'Get track listening statistics'
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'Start date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-01')
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'End date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-31')
    )]
    #[OA\Parameter(
        name: 'track_ids',
        description: 'Comma-separated list of track IDs to filter by (optional)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'track1,track2,track3')
    )]
    #[OA\Response(
        response: 200,
        description: 'Track statistics',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'object', example: ['Track Name' => 15, 'Another Track' => 8], additionalProperties: new OA\AdditionalProperties(type: 'integer')),
                new OA\Property(property: 'total_tracks', type: 'integer', example: 5),
                new OA\Property(property: 'total_listenings', type: 'integer', example: 50),
                new OA\Property(property: 'period', properties: [
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date')
                ], type: 'object'),
                new OA\Property(property: 'filters', properties: [
                    new OA\Property(property: 'track_ids', type: 'array', items: new OA\Items(type: 'string'))
                ], type: 'object')
            ]
        )
    )]
    public function trackStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $trackIds = []
    ): JsonResponse {
        $stats = $this->listeningRepository->getTrackStats(
            $startDate,
            $endDate,
            $trackIds
        );

        return $this->json(['data' => $stats]);
    }

    /**
     * @param SpotifyId[] $playlistIds
     */
    #[Route('/api/listenings/stats/playlists', name: 'api_listeningsapi_listenings_stats_playlists', methods: ['GET'])]
    #[OA\Get(
        path: '/api/listenings/stats/playlists',
        description: 'Retrieve listening statistics for playlists within a date range, optionally filtered by specific playlist IDs',
        summary: 'Get playlist listening statistics'
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'Start date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-01')
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'End date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-31')
    )]
    #[OA\Parameter(
        name: 'playlist_ids',
        description: 'Comma-separated list of playlist IDs to filter by (optional)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'playlist1,playlist2,playlist3')
    )]
    #[OA\Response(
        response: 200,
        description: 'Playlist statistics',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'object', example: ['My Playlist' => 30, 'Another Playlist' => 12], additionalProperties: new OA\AdditionalProperties(type: 'integer')),
                new OA\Property(property: 'total_playlists', type: 'integer', example: 3),
                new OA\Property(property: 'total_listenings', type: 'integer', example: 75),
                new OA\Property(property: 'period', properties: [
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date')
                ], type: 'object'),
                new OA\Property(property: 'filters', properties: [
                    new OA\Property(property: 'playlist_ids', type: 'array', items: new OA\Items(type: 'string'))
                ], type: 'object')
            ]
        )
    )]
    public function playlistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $playlistIds = []
    ): JsonResponse {
        $stats = $this->listeningRepository->getPlaylistStats(
            $startDate,
            $endDate,
            $playlistIds
        );

        return $this->json(['data' => $stats]);
    }

    #[Route('/api/listenings/stats/summary', name: 'api_listeningsapi_listenings_stats_summary', methods: ['GET'])]
    #[OA\Get(
        path: '/api/listenings/stats/summary',
        description: 'Retrieve a comprehensive summary of listening statistics including top artists, tracks, and playlists',
        summary: 'Get listening statistics summary'
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'Start date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-01')
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'End date in YYYY-MM-DD format',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-31')
    )]
    #[OA\Response(
        response: 200,
        description: 'Summary statistics',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'period', properties: [
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date')
                ], type: 'object'),
                new OA\Property(property: 'summary', properties: [
                    new OA\Property(property: 'total_listenings', type: 'integer', example: 500),
                    new OA\Property(property: 'unique_artists', type: 'integer', example: 25),
                    new OA\Property(property: 'unique_tracks', type: 'integer', example: 150),
                    new OA\Property(property: 'unique_playlists', type: 'integer', example: 10)
                ], type: 'object'),
                new OA\Property(property: 'top_artists', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'integer')),
                new OA\Property(property: 'top_tracks', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'integer')),
                new OA\Property(property: 'top_playlists', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'integer'))
            ]
        )
    )]
    public function statsSummary(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): JsonResponse {
        $artistStats = $this->listeningRepository->getArtistStats($startDate, $endDate, []);
        $trackStats = $this->listeningRepository->getTrackStats($startDate, $endDate, []);
        $playlistStats = $this->listeningRepository->getPlaylistStats($startDate, $endDate, []);

        // Limit to top 10 for summary
        $topArtists = array_slice($artistStats, 0, 10, true);
        $topTracks = array_slice($trackStats, 0, 10, true);
        $topPlaylists = array_slice($playlistStats, 0, 10, true);

        return $this->json([
            'summary' => [
                'total_listenings_minutes' => array_sum($artistStats) * 2, // Total from artists (most accurate)
                'unique_artists' => count($artistStats) === ListeningMysqlRepository::MAX_STATS_LIMIT ? -1 : count($artistStats),
                'unique_tracks' => count($trackStats) === ListeningMysqlRepository::MAX_STATS_LIMIT ? -1 : count($trackStats),
                'unique_playlists' => count($playlistStats) === ListeningMysqlRepository::MAX_STATS_LIMIT ? -1 : count($playlistStats),
            ],
            'top_artists' => $topArtists,
            'top_tracks' => $topTracks,
            'top_playlists' => $topPlaylists,
        ]);
    }
}
