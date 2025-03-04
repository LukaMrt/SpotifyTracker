<?php

namespace App\Handler;

use App\Domain\Entity\Artist;
use App\Domain\Entity\Listening;
use App\Domain\Entity\Playlist;
use App\Domain\Entity\SpotifyId;
use App\Domain\Entity\Track;
use App\Domain\Message\StoreListening;
use App\Domain\Repository\ListeningRepositoryInterface;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
class StoreListeningHandler
{
    private const array SCOPES = [
        'user-read-currently-playing',
    ];

    public function __construct(
        protected readonly ListeningRepositoryInterface $listeningRepository,
        protected readonly SpotifyWebAPI $spotifyApi,
        protected readonly Session $session,
        protected readonly DecoderInterface $decoder,
        protected readonly LoggerInterface $logger,
        protected readonly CacheInterface $cache,
    ) {
    }

    protected function connect(): bool
    {
        $this->logger->info('Connecting to Spotify');
        $cacheItem = $this->cache->getItem('spotify_tokens');

        if ($cacheItem->isHit()) {
            $this->logger->info('Cache hit');
            $tokens = $this->decoder->decode($cacheItem->get(), 'json');
            $this->session->setAccessToken($tokens['access_token']);
            $this->session->setRefreshToken($tokens['refresh_token']);
            return true;
        }

        $authorizeUrl = $this->session->getAuthorizeUrl(['scope' => self::SCOPES]);
        $this->logger->error('Cache miss, authorize URL : ' . $authorizeUrl);
        return false;
    }

    public function __invoke(StoreListening $storeListening): void
    {
        $connected = $this->connect();

        if (!$connected) {
            return;
        }

        $this->logger->info('Retrieving current playback info');
        $current = $this->spotifyApi->getMyCurrentTrack();

        if (isset($current['error'])) {
            throw new \RuntimeException($current['error']['message']);
        }

        if (!$current['is_playing']) {
            $this->logger->info('No track is currently playing');
            return;
        }

        $listening = $this->extractListening($current, $storeListening);
        $this->listeningRepository->save($listening);
    }

    protected function extractListening(object|array $current, StoreListening $storeListening): Listening
    {
        $playlist = null;
        $track    = new Track(
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
        $this->logger->info('Track found', [$current['item']['name']]);

        if (isset($current['context']) && $current['context']['type'] === 'playlist') {
            $id       = str_replace('spotify:playlist:', '', $current['context']['uri']);
            $playlist = $this->spotifyApi->getPlaylist($id);
            $this->logger->info('Playlist found', [$playlist['name']]);
            $playlist = new Playlist(
                id: new SpotifyId($playlist['id']),
                name: $playlist['name'],
            );
        }

        return new Listening(
            dateTime: $storeListening->date,
            track: $track,
            playlist: $playlist,
        );
    }
}