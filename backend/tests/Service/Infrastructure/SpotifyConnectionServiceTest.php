<?php

declare(strict_types=1);

namespace App\Tests\Service\Infrastructure;

use App\Domain\Spotify\Api\SpotifyTokens;
use App\Service\Infrastructure\SpotifyConnectionService;
use App\Service\Infrastructure\SpotifyFailureService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Group('unit')]
final class SpotifyConnectionServiceTest extends TestCase
{
    private SpotifyConnectionService $service;
    
    private Session&MockObject $sessionMock;
    
    private CacheInterface&MockObject $cacheMock;
    
    private LoggerInterface&MockObject $loggerMock;
    
    private SpotifyFailureService&MockObject $spotifyFailureServiceMock;
    
    private string $spotifyCode;

    #[\Override]
    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->spotifyFailureServiceMock = $this->createMock(SpotifyFailureService::class);
        $this->spotifyCode = 'test-spotify-code';

        $this->service = new SpotifyConnectionService(
            $this->sessionMock,
            $this->cacheMock,
            $this->loggerMock,
            $this->spotifyCode,
            $this->spotifyFailureServiceMock
        );
    }

    public function testConnect_ShouldReturnTrue_WhenValidTokensFoundInCache(): void
    {
        // Given
        $tokens = new SpotifyTokens('access_token', 'refresh_token');
        $this->cacheMock->method('get')->willReturn($tokens);

        // When
        $result = $this->service->connect();

        // Then
        $this->assertTrue($result);
    }

    public function testConnect_ShouldReturnTrue_WhenAuthenticationSucceeds(): void
    {
        // Given
        $fakeTokens = new SpotifyTokens('fake_access_token', 'fake_refresh_token');
        $this->cacheMock->method('get')->willReturn($fakeTokens);
        $this->sessionMock->method('getAccessToken')->willReturn('new_access_token');
        $this->sessionMock->method('getRefreshToken')->willReturn('new_refresh_token');

        // Then
        $this->sessionMock->expects($this->once())->method('requestAccessToken')->with($this->spotifyCode);

        // When
        $result = $this->service->connect();

        // Then
        $this->assertTrue($result);
    }

    public function testConnect_ShouldReturnFalse_WhenSpotifyCodeIsEmpty(): void
    {
        // Given
        $service = new SpotifyConnectionService(
            $this->sessionMock,
            $this->cacheMock,
            $this->loggerMock,
            '',
            $this->spotifyFailureServiceMock
        );
        $fakeTokens = new SpotifyTokens('fake_access_token', 'fake_refresh_token');
        $this->cacheMock->method('get')->willReturn($fakeTokens);

        // When
        $result = $service->connect();

        // Then
        $this->assertFalse($result);
    }

    public function testConnect_ShouldLogFailure_WhenAuthenticationFails(): void
    {
        // Given
        $fakeTokens = new SpotifyTokens('fake_access_token', 'fake_refresh_token');
        $this->cacheMock->method('get')->willReturn($fakeTokens);
        $this->sessionMock->method('requestAccessToken')->willThrowException(new \Exception());

        // Then
        $this->spotifyFailureServiceMock->expects($this->once())->method('logConnectionFailure');

        // When
        $result = $this->service->connect();

        // Then
        $this->assertFalse($result);
    }

    public function testSaveTokens_ShouldClearCacheBeforeSaving(): void
    {
        // Given
        $this->sessionMock->method('getAccessToken')->willReturn('access_token');
        $this->sessionMock->method('getRefreshToken')->willReturn('refresh_token');
        $this->cacheMock->method('get')->willReturnCallback(fn(string $key, callable $callback): mixed =>
            /** @var callable $callback */
            $callback($this->createMock(ItemInterface::class)));

        // Then
        $this->cacheMock->expects($this->once())->method('delete')->with(SpotifyConnectionService::CACHE_TOKENS_KEY);

        // When
        $this->service->saveTokens();
    }

    public function testClearTokens_ShouldDeleteTokensFromCache(): void
    {
        // Then
        $this->cacheMock->expects($this->once())->method('delete')->with(SpotifyConnectionService::CACHE_TOKENS_KEY);

        // When
        $this->service->clearTokens();
    }
}