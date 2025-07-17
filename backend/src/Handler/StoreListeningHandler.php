<?php

declare(strict_types=1);

namespace App\Handler;

use App\Domain\Api\ApiArtist;
use App\Domain\Api\ApiListening;
use App\Domain\Api\ApiPlaylist;
use App\Domain\Entity\Artist;
use App\Domain\Entity\Listening;
use App\Domain\Entity\Playlist;
use App\Domain\Entity\SpotifyId;
use App\Domain\Entity\Track;
use App\Domain\Message\StoreListening;
use App\Domain\Repository\ListeningRepositoryInterface;
use App\Domain\Service\SpotifyConnectionService;
use App\Domain\Service\SpotifyFailureService;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsMessageHandler]
class StoreListeningHandler
{
    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository,
        private readonly SpotifyWebAPI                $spotifyApi,
        private readonly SpotifyConnectionService     $connectionService,
        private readonly SpotifyFailureService        $failureService,
        private readonly LoggerInterface              $logger,
        private readonly DenormalizerInterface        $denormalizer,
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

            if ($current->is_playing !== true) {
                $this->logger->info('No track is currently playing');
                return;
            }

            $listening = $this->extractListening($current, $storeListening);
            $this->listeningRepository->save($listening);
            $this->connectionService->saveTokens();

        } catch (\Throwable $throwable) {
            if ($throwable->getCode() === Response::HTTP_UNAUTHORIZED) {
                $this->logger->error('Spotify API returned 401 Unauthorized, clearing tokens');
                $this->failureService->handleFailure(false);
                return;
            }
            
            $this->handleError($throwable);
        }
    }

    private function getCurrentTrack(): ApiListening
    {
        $this->logger->info('Retrieving current playback info');
        $current = $this->denormalizer->denormalize($this->spotifyApi->getMyCurrentTrack(), ApiListening::class);
        assert($current instanceof ApiListening, 'Current playback should be an ApiListening instance');

        if ($current->error instanceof \App\Domain\Api\ApiError) {
            throw new \RuntimeException($current->error->message ?? 'Unknown error while retrieving current playback');
        }

        return $current;
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

    private function extractListening(ApiListening $current, StoreListening $storeListening): Listening
    {
        $track = $this->createTrackFromApiResponse($current);
        $playlist = $this->createPlaylistFromApiResponse($current);

        return new Listening(
            dateTime: $storeListening->date,
            track: $track,
            playlist: $playlist,
        );
    }

    private function createTrackFromApiResponse(ApiListening $current): Track
    {
        assert($current->item instanceof \App\Domain\Api\ApiListeningItem, 'Current item should not be null');
        $track = new Track(
            id: new SpotifyId($current->item->id),
            name: $current->item->name,
            artists: array_map(
                static fn(ApiArtist $artist): \App\Domain\Entity\Artist => new Artist(
                    id: new SpotifyId($artist->id),
                    name: $artist->name,
                ),
                $current->item->artists,
            ),
        );

        $this->logger->info('Track found', ['track' => $current->item->name]);
        return $track;
    }

    private function createPlaylistFromApiResponse(ApiListening $current): ?Playlist
    {
        if (!$current->context instanceof \App\Domain\Api\ApiListeningContext || $current->context->type !== 'playlist') {
            return null;
        }

        $playlistId = str_replace('spotify:playlist:', '', $current->context->uri ?? '');
        try {
            $apiPlaylist = $this->denormalizer->denormalize($this->spotifyApi->getPlaylist($playlistId), ApiPlaylist::class);
            assert($apiPlaylist instanceof ApiPlaylist, 'Playlist should be an ApiPlaylist');

            $this->logger->info('Playlist found', ['playlist' => $apiPlaylist->name]);

            return new Playlist(
                id: new SpotifyId($apiPlaylist->id),
                name: $apiPlaylist->name,
            );
        } catch (\Throwable) {
            $this->logger->info('Unknown playlist', [
                'playlistId' => $playlistId,
            ]);
            return null;
        }
    }
}
