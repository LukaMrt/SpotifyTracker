<?php

declare(strict_types=1);

namespace App\Service\Domain;

use App\Domain\Spotify\Api\ApiError;
use App\Domain\Spotify\Api\ApiListening;
use App\Domain\Spotify\Api\ApiListeningContext;
use App\Domain\Spotify\Api\ApiListeningItem;
use App\Domain\Spotify\Api\ApiPlaylist;
use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\Playlist;
use App\Domain\Spotify\Entity\Track;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use App\Service\Infrastructure\SpotifyConnectionService;
use App\Service\Infrastructure\SpotifyFailureService;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
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
        private readonly ObjectMapperInterface $objectMapper,
    ) {
    }

    public function storeCurrentTrack(): void
    {
        try {
            $this->storeCurrentListening();
        } catch (\Throwable $throwable) {
            $this->handleFailure($throwable);
        }
    }

    private function storeCurrentListening(): void
    {
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

        if (!$current->item instanceof ApiListeningItem || !$current->playlist instanceof ApiPlaylist) {
            $this->logger->info('Current playback info is incomplete, skipping');
            return;
        }

        $listening = new Listening(
            dateTime: new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone(self::TIMEZONE)),
            track: $this->objectMapper->map($current->item, Track::class),
            playlist: $this->objectMapper->map($current->playlist, Playlist::class),
        );
        $this->listeningRepository->save($listening);
        $this->connectionService->saveTokens();
    }

    private function getCurrentTrack(): ApiListening
    {
        $this->logger->info('Retrieving current playback info');
        $current = $this->denormalizer->denormalize($this->spotifyApi->getMyCurrentTrack(), ApiListening::class);
        assert($current instanceof ApiListening, 'Current playback should be an ApiListening instance');

        if ($current->error instanceof ApiError) {
            throw new \RuntimeException($current->error->message ?? 'Unknown error while retrieving current playback');
        }

        if (
            $current->is_playing === true
            && $current->context instanceof ApiListeningContext
            && $current->context->type === 'playlist'
        ) {
            try {
                $current = $current->withPlaylist($this->findPlaylist($current));
            } catch (\Throwable) {
                $this->logger->info('Unknown playlist');
            }
        }

        return $current;
    }

    private function findPlaylist(ApiListening $current): ApiPlaylist
    {
        $playlistId = str_replace('spotify:playlist:', '', $current->context->uri ?? '');
        $apiPlaylist = $this->denormalizer->denormalize($this->spotifyApi->getPlaylist($playlistId), ApiPlaylist::class);
        assert($apiPlaylist instanceof ApiPlaylist, 'Playlist should be an ApiPlaylist');
        return $apiPlaylist;
    }

    public function handleFailure(\Throwable|\Exception $throwable): void
    {
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

        throw $throwable;
    }
}
