<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Spotify\Api\ApiArtist;
use App\Domain\Spotify\Api\ApiError;
use App\Domain\Spotify\Api\ApiListening;
use App\Domain\Spotify\Api\ApiListeningContext;
use App\Domain\Spotify\Api\ApiListeningItem;
use App\Domain\Spotify\Api\ApiPlaylist;
use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\Playlist;
use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Entity\Track;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ListeningService
{
    protected const string TIMEZONE = 'Europe/Paris';

    public function __construct(
        private readonly ListeningRepositoryInterface $listeningRepository,
        private readonly SpotifyWebAPI $spotifyApi,
        private readonly SpotifyConnectionService $connectionService,
        private readonly SpotifyFailureService $failureService,
        private readonly LoggerInterface $logger,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function storeCurrentTrack(): void
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

            $listening = $this->extractListening($current);
            $this->listeningRepository->save($listening);
            $this->connectionService->saveTokens();

        } catch (\Throwable $throwable) {
            $this->logger->error('Error while retrieving current playback info', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);

            $this->failureService->handleFailure(true);
            $this->connectionService->saveTokens();

            if (!is_int($throwable->getCode())) {
                return;
            }

            if ($throwable->getCode() === Response::HTTP_UNAUTHORIZED) {
                $this->logger->error('Spotify API returned 401 Unauthorized, clearing tokens');
                $this->connectionService->clearTokens();
            }

            if ($throwable->getCode() / 100 === 5) {
                $this->logger->error('Spotify API returned a server error', [
                    'message' => $throwable->getMessage(),
                    'code' => $throwable->getCode(),
                ]);
            }
        }
    }

    private function getCurrentTrack(): ApiListening
    {
        $this->logger->info('Retrieving current playback info');
        $current = $this->denormalizer->denormalize($this->spotifyApi->getMyCurrentTrack(), ApiListening::class);
        assert($current instanceof ApiListening, 'Current playback should be an ApiListening instance');

        if ($current->error instanceof ApiError) {
            throw new \RuntimeException($current->error->message ?? 'Unknown error while retrieving current playback');
        }

        return $current;
    }

    private function extractListening(ApiListening $current): Listening
    {
        $track = $this->createTrackFromApiResponse($current);
        $playlist = $this->createPlaylistFromApiResponse($current);

        return new Listening(
            dateTime: new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone(self::TIMEZONE)),
            track: $track,
            playlist: $playlist,
        );
    }

    private function createTrackFromApiResponse(ApiListening $current): Track
    {
        assert($current->item instanceof ApiListeningItem, 'Current item should not be null');
        $track = new Track(
            id: new SpotifyId($current->item->id),
            name: $current->item->name,
            artists: array_map(
                static fn(ApiArtist $artist): Artist => new Artist(
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
        if (!$current->context instanceof ApiListeningContext || $current->context->type !== 'playlist') {
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
