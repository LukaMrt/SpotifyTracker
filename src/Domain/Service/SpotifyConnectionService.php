<?php

namespace App\Domain\Service;

use App\Domain\Api\FakeSpotifyTokens;
use App\Domain\Api\SpotifyTokens;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SpotifyConnectionService
{
    public const string CACHE_TOKENS_KEY = 'spotify_tokens';
    public const int CACHE_TOKENS_EXPIRATION = 86_400; // 1 day

    public function __construct(
        private readonly Session         $session,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly string          $spotifyCode,
        private readonly SpotifyFailureService $spotifyFailureService,
    ) {
    }

    public function connect(): bool
    {
        $this->logger->info('Connecting to Spotify');

        $tokens = $this->getTokensFromCache();
        if ($tokens !== null) {
            $this->setSessionTokens($tokens);
            return true;
        }

        return $this->authenticateWithCode();
    }

    private function getTokensFromCache(): ?SpotifyTokens
    {
        $tokens = $this->cache->get(
            self::CACHE_TOKENS_KEY,
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TOKENS_EXPIRATION);
                return new SpotifyTokens('fake_access_token', 'fake_refresh_token');
            }
        );

        if ($tokens->getAccessToken() === 'fake_access_token') {
            $this->logger->info('Cache miss, no tokens found');
            return null;
        }

        return $tokens;
    }

    private function authenticateWithCode(): bool
    {
        if ($this->spotifyCode === '') {
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
        $this->cache->delete(self::CACHE_TOKENS_KEY);
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
