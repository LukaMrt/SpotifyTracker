<?php

namespace App\Domain\Service\Spotify;

use App\Domain\Entity\SpotifyTokens;
use App\Domain\Service\Spotify\SpotifyFailureService;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SpotifyConnectionService
{
    private const string CACHE_TOKENS_KEY = 'spotify_tokens';
    private const int CACHE_TOKENS_EXPIRATION = 86_400; // 1 day

    public function __construct(
        private readonly Session         $session,
        private readonly CacheInterface  $cache,
        private readonly LoggerInterface $logger,
        private readonly string          $spotifyCode, private readonly SpotifyFailureService $spotifyFailureService,
    ) {
    }

    public function connect(): bool
    {
        $this->logger->info('Connecting to Spotify');

        $tokens = $this->getTokensFromCache();
        if ($tokens) {
            $this->setSessionTokens($tokens);
            return true;
        }

        return $this->authenticateWithCode();
    }

    private function getTokensFromCache(): ?SpotifyTokens
    {
        $cacheItem = $this->cache->getItem(self::CACHE_TOKENS_KEY);

        if ($cacheItem->isHit()) {
            $this->logger->info('Cache hit');
            return $cacheItem->get();
        }

        return null;
    }

    private function authenticateWithCode(): bool
    {
        if (empty($this->spotifyCode)) {
            return false;
        }

        try {
            $this->logger->info('Spotify code provided, requesting access token');
            $this->session->requestAccessToken($this->spotifyCode);
            $this->saveTokens();
        } catch (\Exception $e) {
            $this->logger->error('Invalid Spotify code provided: ' . $e->getMessage());
            $this->spotifyFailureService->logConnectionFailure();
            return false;
        }
        return true;
    }

    public function saveTokens(): void
    {
        $this->cache->get(
            self::CACHE_TOKENS_KEY,
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TOKENS_EXPIRATION);
                return new SpotifyTokens(
                    accessToken: $this->session->getAccessToken(),
                    refreshToken: $this->session->getRefreshToken()
                );
            }
        );
    }

    public function clearTokens(): void
    {
        $this->cache->delete(self::CACHE_TOKENS_KEY);
        $this->logger->info('Spotify failure cache cleared');
    }

    private function setSessionTokens(SpotifyTokens $tokens): void
    {
        $this->session->setAccessToken($tokens->getAccessToken());
        $this->session->setRefreshToken($tokens->getRefreshToken());
    }
}
