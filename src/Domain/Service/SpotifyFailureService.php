<?php

namespace App\Domain\Service;

use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Cache\CacheInterface;

class SpotifyFailureService
{
    private const int FAIL_COUNT_BEFORE_NOTIFICATION = 1;
    private const int DELAY_BETWEEN_NOTIFICATIONS = 1_800; // 30 minutes
    private const int CACHE_FAILURE_EXPIRE_TIME = 3_600; // 1 hour
    private const string CACHE_FAIL_COUNT_KEY = 'spotify_fail_count';
    private const string CACHE_LAST_NOTIFICATION_KEY = 'spotify_fail_last_notification';
    private const array SCOPES = ['user-read-currently-playing'];

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly Session $session,
        private readonly string $adminEmailAddress,
    ) {
    }

    public function handleFailure(bool $connected): void
    {
        if (!$connected) {
            $this->logConnectionFailure();
        }

        $failCount = $this->incrementFailCount();

        if ($this->shouldSendNotification($failCount)) {
            $this->sendFailureNotification();
        }
    }

    public function clearFailCount(): void
    {
        $this->cache->delete(self::CACHE_FAIL_COUNT_KEY);
    }

    public function logConnectionFailure(): void
    {
        $this->logger->info('Spotify API not connected');
        $authorizeUrl = $this->session->getAuthorizeUrl(['scope' => self::SCOPES]);
        $this->logger->error('Authorize URL : ' . $authorizeUrl);
    }

    private function incrementFailCount(): int
    {
        $failCount = $this->cache->get(
            self::CACHE_FAIL_COUNT_KEY,
            static fn(CacheItemInterface $item) => self::initializeCacheItem($item, 0)
        );
        assert(is_int($failCount), 'Fail count must be an integer');

        $failCount++;
        $this->cache->delete(self::CACHE_FAIL_COUNT_KEY);

        $this->cache->get(
            self::CACHE_FAIL_COUNT_KEY,
            static fn(CacheItemInterface $item) => self::initializeCacheItem($item, $failCount)
        );

        $this->logger->info('Fail count : ' . $failCount);
        return $failCount;
    }

    private function shouldSendNotification(int $failCount): bool
    {
        if ($failCount <= self::FAIL_COUNT_BEFORE_NOTIFICATION) {
            return false;
        }

        $lastNotification = $this->cache->get(
            self::CACHE_LAST_NOTIFICATION_KEY,
            static fn(CacheItemInterface $item) => self::initializeCacheItem($item, 0)
        );
        assert(is_int($lastNotification), 'Last notification time must be an integer');

        $this->logger->info('Last notification : ' . (new \DateTimeImmutable('@' . $lastNotification))->format('Y-m-d H:i:s'));

        return (time() - $lastNotification) >= self::DELAY_BETWEEN_NOTIFICATIONS;
    }

    private function sendFailureNotification(): void
    {
        $this->updateLastNotificationTime();

        $email = (new Email())
            ->subject('Spotify tracker not connected')
            ->text('Spotify tracker not connected for too long')
            ->to($this->adminEmailAddress);

        $this->logger->error('Tracker not connected for too long, sending notification');
        $this->mailer->send($email);
    }

    private function updateLastNotificationTime(): void
    {
        $this->cache->delete(self::CACHE_LAST_NOTIFICATION_KEY);
        $this->cache->get(
            self::CACHE_LAST_NOTIFICATION_KEY,
            static fn(CacheItemInterface $item) => self::initializeCacheItem($item, time())
        );
    }

    private static function initializeCacheItem(CacheItemInterface $item, mixed $value): mixed
    {
        $item->expiresAfter(self::CACHE_FAILURE_EXPIRE_TIME);
        return $value;
    }
}
