<?php

declare(strict_types=1);

namespace App\Tests\Api;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Group('e2e')]
final class ListeningApiTest extends WebTestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::getClient(self::createClient());
    }

    /**
     * Helper method to get the client with the proper type assertion
     */
    private function getTestClient(): KernelBrowser
    {
        $client = self::getClient();
        $this->assertInstanceOf(KernelBrowser::class, $client);
        return $client;
    }

    /**
     * Helper method to decode JSON response safely
     * @return array<mixed>
     */
    private function getJsonResponse(): array
    {
        $content = $this->getTestClient()->getResponse()->getContent();
        $this->assertIsString($content);
        
        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        
        return $decoded;
    }

    /**
     * Helper method to get status code safely
     */
    private function getStatusCode(): int
    {
        return $this->getTestClient()->getResponse()->getStatusCode();
    }

    // GET /api/listenings tests
    
    public function testGetListenings_ShouldReturn200_WhenValidDatesProvided(): void
    {
        // Given
        $startDate = '2025-01-01';
        $endDate = '2025-01-31';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('count', $responseData);
        $this->assertIsInt($responseData['count']);
        $this->assertGreaterThanOrEqual(0, $responseData['count']);
    }

    public function testGetListenings_ShouldReturn200_WhenFiltersApplied(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';
        $playlistId = 'test-playlist-id';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'playlist_id' => $playlistId,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('count', $responseData);
        $this->assertIsInt($responseData['count']);
    }

    public function testGetListenings_ShouldReturn400_WhenMissingRequiredParams(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings');

        // Then
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetListenings_ShouldReturn400_WhenInvalidDateFormat(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings', [
            'start_date' => 'invalid-date',
            'end_date' => '2025-01-31',
        ]);

        // Then
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    // GET /api/listenings/stats/artists tests
    
    public function testGetArtistStats_ShouldReturn200_WhenValidRequest(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/artists', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check structure if data exists
        if (isset($responseData['data']) && $responseData['data'] !== []) {
            foreach ($responseData['data'] as $artistName => $count) {
                $this->assertIsString($artistName);
                $this->assertIsInt($count);
                $this->assertGreaterThan(0, $count);
            }
        }
    }

    public function testGetArtistStats_ShouldReturn200_WhenFilteredByArtistIds(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';
        $artistIds = 'artist1,artist2';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/artists', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'artist_ids' => $artistIds,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('data', $responseData);
    }

    // GET /api/listenings/stats/tracks tests
    
    public function testGetTrackStats_ShouldReturn200_WhenValidRequest(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/tracks', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check structure if data exists
        if (isset($responseData['data']) && $responseData['data'] !== []) {
            foreach ($responseData['data'] as $trackName => $count) {
                $this->assertIsString($trackName);
                $this->assertIsInt($count);
                $this->assertGreaterThan(0, $count);
            }
        }
    }

    public function testGetTrackStats_ShouldReturn200_WhenFilteredByTrackIds(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';
        $trackIds = 'track1,track2';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/tracks', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'track_ids' => $trackIds,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('data', $responseData);
    }

    // GET /api/listenings/stats/playlists tests
    
    public function testGetPlaylistStats_ShouldReturn200_WhenValidRequest(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/playlists', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check structure if data exists
        if (isset($responseData['data']) && $responseData['data'] !== []) {
            foreach ($responseData['data'] as $playlistName => $count) {
                $this->assertIsString($playlistName);
                $this->assertIsInt($count);
                $this->assertGreaterThan(0, $count);
            }
        }
    }

    public function testGetPlaylistStats_ShouldReturn200_WhenFilteredByPlaylistIds(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';
        $playlistIds = 'playlist1,playlist2';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/playlists', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'playlist_ids' => $playlistIds,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        $this->assertArrayHasKey('data', $responseData);
    }

    // GET /api/listenings/stats/summary tests
    
    public function testGetStatsSummary_ShouldReturn200_WhenValidRequest(): void
    {
        // Given
        $startDate = '2024-12-01';
        $endDate = '2025-01-31';

        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/summary', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Then
        $this->assertResponseIsSuccessful();
        
        $responseData = $this->getJsonResponse();
        
        // Check required structure
        $this->assertArrayHasKey('summary', $responseData);
        $this->assertArrayHasKey('top_artists', $responseData);
        $this->assertArrayHasKey('top_tracks', $responseData);
        $this->assertArrayHasKey('top_playlists', $responseData);
        
        // Check summary structure
        $summary = $responseData['summary'];
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_listenings_minutes', $summary);
        $this->assertArrayHasKey('unique_artists', $summary);
        $this->assertArrayHasKey('unique_tracks', $summary);
        $this->assertArrayHasKey('unique_playlists', $summary);
        
        // Check data types
        $this->assertIsInt($summary['total_listenings_minutes']);
        $this->assertIsInt($summary['unique_artists']);
        $this->assertIsInt($summary['unique_tracks']);
        $this->assertIsInt($summary['unique_playlists']);
        
        // Check that arrays are properly structured
        $this->assertIsArray($responseData['top_artists']);
        $this->assertIsArray($responseData['top_tracks']);
        $this->assertIsArray($responseData['top_playlists']);
    }

    public function testGetStatsSummary_ShouldReturn400_WhenMissingDates(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/listenings/stats/summary');

        // Then
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    // Test common error scenarios
    
    public function testAllEndpoints_ShouldReturn200_WhenStartDateAfterEndDate(): void
    {
        // Given
        $endpoints = [
            '/api/listenings',
            '/api/listenings/stats/artists',
            '/api/listenings/stats/tracks',
            '/api/listenings/stats/playlists',
            '/api/listenings/stats/summary',
        ];

        foreach ($endpoints as $endpoint) {
            // When
            $this->getTestClient()->request(Request::METHOD_GET, $endpoint, [
                'start_date' => '2025-02-01',
                'end_date' => '2025-01-01', // End before start
            ]);

            // Then - API returns 200 with empty results (valid behavior)
            $this->assertResponseIsSuccessful(
                sprintf('Endpoint %s should return 200 even when start_date is after end_date', $endpoint)
            );
            
            // Verify response structure
            $responseData = $this->getJsonResponse();
            if (str_contains($endpoint, '/stats/summary')) {
                $this->assertArrayHasKey('summary', $responseData);
            } elseif (str_contains($endpoint, '/stats/')) {
                $this->assertArrayHasKey('data', $responseData);
            } else {
                $this->assertArrayHasKey('count', $responseData);
            }
        }
    }

    public function testAllEndpoints_ShouldHaveCorrectContentType(): void
    {
        $endpoints = [
            '/api/listenings',
            '/api/listenings/stats/artists',
            '/api/listenings/stats/tracks',
            '/api/listenings/stats/playlists',
            '/api/listenings/stats/summary',
        ];

        foreach ($endpoints as $endpoint) {
            // When
            $this->getTestClient()->request(Request::METHOD_GET, $endpoint, [
                'start_date' => '2024-12-01',
                'end_date' => '2025-01-31',
            ]);

            // Then
            if ($this->getStatusCode() === Response::HTTP_OK) {
                $this->assertResponseHeaderSame(
                    'Content-Type',
                    'application/json',
                    sprintf('Endpoint %s should return JSON content type', $endpoint)
                );
            }
            
            // Reset client for next request
            $this->getTestClient()->restart();
        }
    }
}