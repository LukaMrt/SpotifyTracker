<?php

namespace App\Handler;

use App\Domain\Entity\Artist;
use App\Domain\Entity\Listening;
use App\Domain\Entity\Playlist;
use App\Domain\Entity\SpotifyId;
use App\Domain\Entity\Track;
use App\Domain\Message\StoreListening;
use App\Domain\Repository\ListeningRepositoryInterface;
use App\Domain\Service\Spotify\SpotifyConnectionService;
use App\Domain\Service\Spotify\SpotifyFailureService;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreListeningHandler
{
    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository,
        private readonly SpotifyWebAPI $spotifyApi,
        private readonly SpotifyConnectionService $connectionService,
        private readonly SpotifyFailureService $failureService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(StoreListening $storeListening): void
    {
        try {
            if (!$this->connectionService->connect()) {
                $this->failureService->handleFailure(false);
                return;
            }

            $this->failureService->clearFailCount();
            $current = $this->getCurrentTrack();

            if (!$this->isTrackPlaying($current)) {
                $this->logger->info('No track is currently playing');
                return;
            }

            $listening = $this->extractListening($current, $storeListening);
            $this->listeningRepository->save($listening);
            $this->connectionService->saveTokens();

        } catch (\Throwable $e) {
            if ($e->getCode() === Response::HTTP_UNAUTHORIZED) {
                $this->logger->error('Spotify API returned 401 Unauthorized, clearing tokens');
                $this->failureService->handleFailure(false);
                $this->connectionService->clearTokens();
                return;
            }
            $this->handleError($e);
        }
    }

    private function getCurrentTrack(): array
    {
        $this->logger->info('Retrieving current playback info');
        $current = $this->spotifyApi->getMyCurrentTrack();

        if (isset($current['error'])) {
            throw new \RuntimeException($current['error']['message']);
        }

        return $current;
    }

    private function isTrackPlaying(?array $current): bool
    {
        return $current !== null && ($current['is_playing'] ?? false);
    }

    private function handleError(\Throwable $e): void
    {
        $this->logger->error('Error while retrieving current playback info', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->failureService->handleFailure(true);
        $this->connectionService->saveTokens();
    }

    private function extractListening(array $current, StoreListening $storeListening): Listening
    {
        $track = $this->createTrackFromApiResponse($current);
        $playlist = $this->createPlaylistFromApiResponse($current);

        return new Listening(
            dateTime: $storeListening->date,
            track: $track,
            playlist: $playlist,
        );
    }

    private function createTrackFromApiResponse(array $current): Track
    {
        $track = new Track(
            id: new SpotifyId($current['item']['id']),
            name: $current['item']['name'],
            artists: array_map(
                static fn($artist) => new Artist(
                    id: new SpotifyId($artist['id']),
                    name: $artist['name'],
                ),
                $current['item']['artists'],
            ),
        );

        $this->logger->info('Track found', ['track' => $current['item']['name']]);
        return $track;
    }

    private function createPlaylistFromApiResponse(array $current): ?Playlist
    {
        if (!isset($current['context']) || $current['context']['type'] !== 'playlist') {
            return null;
        }

        try {
            $playlistId = $this->extractPlaylistId($current['context']['uri']);
            $playlistData = $this->spotifyApi->getPlaylist($playlistId);

            $this->logger->info('Playlist found', ['playlist' => $playlistData['name']]);

            return new Playlist(
                id: new SpotifyId($playlistData['id']),
                name: $playlistData['name'],
            );
        } catch (\Throwable) {
            $this->logger->info('Unknown playlist', [
                'playlistId' => $this->extractPlaylistId($current['context']['uri']),
            ]);
            return null;
        }
    }

    private function extractPlaylistId(string $uri): string
    {
        return str_replace('spotify:playlist:', '', $uri);
    }
}
