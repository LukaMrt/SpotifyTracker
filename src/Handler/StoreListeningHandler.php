<?php

namespace App\Handler;

use App\Domain\Entity\Artist;
use App\Domain\Entity\Listening;
use App\Domain\Entity\Playlist;
use App\Domain\Entity\SpotifyId;
use App\Domain\Entity\Track;
use App\Domain\Message\StoreListening;
use App\Domain\Repository\ListeningRepositoryInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
class StoreListeningHandler
{
    protected const array SCOPES = [
        'user-read-currently-playing',
    ];

    protected const int    FAIL_COUNT_BEFORE_NOTIFICATION = 1;
    protected const int    DELAY_BETWEEN_NOTIFICATIONS    = 1_800; // 30 minutes
    protected const int    CACHE_FAILURE_EXPIRE_TIME      = 3_600; // 1 hour
    protected const string CACHE_FAIL_COUNT_KEY           = 'spotify_fail_count';
    protected const string CACHE_LAST_NOTIFICATION_KEY    = 'spotify_fail_last_notification';
    public    const string CACHE_TOKENS_KEY               = 'spotify_tokens';

    public function __construct(
        protected readonly ListeningRepositoryInterface $listeningRepository,
        protected readonly SpotifyWebAPI $spotifyApi,
        protected readonly Session $session,
        protected readonly DecoderInterface $decoder,
        protected readonly LoggerInterface $logger,
        protected readonly CacheInterface $cache,
        protected readonly MailerInterface $mailer,
        protected readonly string $adminEmailAddress,
    ) {
    }

    protected function connect(): bool
    {
        $this->logger->info('Connecting to Spotify');
        $cacheItem = $this->cache->getItem(self::CACHE_TOKENS_KEY);

        if ($cacheItem->isHit()) {
            $this->logger->info('Cache hit');
            $tokens = $this->decoder->decode($cacheItem->get(), 'json');
            $this->session->setAccessToken($tokens['access_token']);
            $this->session->setRefreshToken($tokens['refresh_token']);
            return true;
        }

        return false;
    }

    public function __invoke(StoreListening $storeListening): void
    {
        $connected = $this->connect();

        if (!$connected) {
            $this->handleFailure();
            return;
        }

        $this->cache->delete(self::CACHE_FAIL_COUNT_KEY);
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

    public function handleFailure(): void
    {
        $this->logger->info('Spotify API not connected');
        $authorizeUrl = $this->session->getAuthorizeUrl(['scope' => self::SCOPES]);
        $this->logger->error('Authorize URL : ' . $authorizeUrl);
        $failCount = $this->cache->get(
            self::CACHE_FAIL_COUNT_KEY,
            static function (CacheItemInterface $item) {
                $item->expiresAfter(self::CACHE_FAILURE_EXPIRE_TIME); // 1 hour
                return 0;
            }
        );
        $failCount++;
        $this->cache->delete(self::CACHE_FAIL_COUNT_KEY);
        $this->cache->get(
            self::CACHE_FAIL_COUNT_KEY,
            static function (CacheItemInterface $item) use ($failCount) {
                $item->expiresAfter(self::CACHE_FAILURE_EXPIRE_TIME); // 1 hour
                return $failCount;
            }
        );

        $this->logger->info('Fail count : ' . $failCount);
        if ($failCount <= self::FAIL_COUNT_BEFORE_NOTIFICATION) {
            return;
        }

        $lastNotification = $this->cache->get(
            self::CACHE_LAST_NOTIFICATION_KEY,
            static function (CacheItemInterface $item) {
                $item->expiresAfter(self::CACHE_FAILURE_EXPIRE_TIME); // 1 hour
                return 0;
            }
        );

        $this->logger->info('Last notification : ' . (new \DateTimeImmutable('@' . $lastNotification))->format('Y-m-d H:i:s'));
        if (time() - $lastNotification < self::DELAY_BETWEEN_NOTIFICATIONS) { // 30 minutes
            return;
        }

        $this->cache->delete(self::CACHE_LAST_NOTIFICATION_KEY);
        $this->cache->get(
            self::CACHE_LAST_NOTIFICATION_KEY,
            static function (CacheItemInterface $item) {
                $item->expiresAfter(self::CACHE_FAILURE_EXPIRE_TIME); // 1 hour
                return time();
            }
        );

        $email = (new Email())
            ->subject('Spotify tracker not connected')
            ->text('Spotify tracker not connected for too long')
            ->to($this->adminEmailAddress);

        $this->logger->error('Tracker not connected for too long, sending notification');
        $this->mailer->send($email);
    }
}