<?php

declare(strict_types=1);

namespace App\Tests\Service\Domain;

use App\Domain\Spotify\Api\ApiError;
use App\Domain\Spotify\Api\ApiListening;
use App\Domain\Spotify\Api\ApiListeningItem;
use App\Domain\Spotify\Api\ApiPlaylist;
use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\Playlist;
use App\Domain\Spotify\Entity\Track;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use App\Service\Domain\ListeningService;
use App\Service\Infrastructure\SpotifyConnectionService;
use App\Service\Infrastructure\SpotifyFailureService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Group('unit')]
final class ListeningServiceTest extends TestCase
{
    private ListeningService $service;
    
    private ListeningRepositoryInterface&MockObject $repositoryMock;
    
    private SpotifyWebAPI&MockObject $spotifyApiMock;
    
    private SpotifyConnectionService&MockObject $connectionServiceMock;
    
    private SpotifyFailureService&MockObject $failureServiceMock;
    
    private DenormalizerInterface&MockObject $denormalizerMock;
    
    private ObjectMapperInterface&MockObject $objectMapperMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ListeningRepositoryInterface::class);
        $this->spotifyApiMock = $this->createMock(SpotifyWebAPI::class);
        $this->connectionServiceMock = $this->createMock(SpotifyConnectionService::class);
        $this->failureServiceMock = $this->createMock(SpotifyFailureService::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->denormalizerMock = $this->createMock(DenormalizerInterface::class);
        $this->objectMapperMock = $this->createMock(ObjectMapperInterface::class);

        $this->service = new ListeningService(
            $this->repositoryMock,
            $this->spotifyApiMock,
            $this->connectionServiceMock,
            $this->failureServiceMock,
            $loggerMock,
            $this->denormalizerMock,
            $this->objectMapperMock
        );
    }

    public function testStoreCurrentTrack_ShouldHandleFailure_WhenConnectionFails(): void
    {
        // Given
        $this->connectionServiceMock->method('connect')->willReturn(false);

        // Then
        $this->failureServiceMock->expects($this->once())->method('handleFailure')->with(false);

        // When
        $this->service->storeCurrentTrack();
    }

    public function testStoreCurrentTrack_ShouldSaveListening_WhenTrackIsPlaying(): void
    {
        // Given
        $this->connectionServiceMock->method('connect')->willReturn(true);
        
        $item = $this->createMock(ApiListeningItem::class);
        $playlist = $this->createMock(ApiPlaylist::class);
        $apiListening = new ApiListening(
            is_playing: true,
            item: $item,
            playlist: $playlist
        );
        
        $this->denormalizerMock->method('denormalize')->willReturn($apiListening);
        $this->spotifyApiMock->method('getMyCurrentTrack')->willReturn([]);
        $this->objectMapperMock->method('map')->willReturnOnConsecutiveCalls(
            $this->createMock(Track::class),
            $this->createMock(Playlist::class)
        );

        // Then
        $this->repositoryMock->expects($this->once())->method('save')
            ->with(self::isInstanceOf(Listening::class));
        $this->connectionServiceMock->expects($this->once())->method('saveTokens');

        // When
        $this->service->storeCurrentTrack();
    }

    public function testStoreCurrentTrack_ShouldNotSave_WhenPlaybackInfoIncomplete(): void
    {
        // Given
        $this->connectionServiceMock->method('connect')->willReturn(true);
        $apiListening = new ApiListening(
            is_playing: true,
            item: null,
            playlist: null
        );
        $this->denormalizerMock->method('denormalize')->willReturn($apiListening);
        $this->spotifyApiMock->method('getMyCurrentTrack')->willReturn([]);

        // Then
        $this->repositoryMock->expects($this->never())->method('save');

        // When
        $this->service->storeCurrentTrack();
    }

    public function testHandleFailure_ShouldCallServices_WhenCalled(): void
    {
        // Given
        $exception = new \Exception('Test error');

        // Then
        $this->failureServiceMock->expects($this->once())->method('handleFailure')->with(true);
        $this->connectionServiceMock->expects($this->once())->method('saveTokens');

        // When & Then
        $this->expectException(\Exception::class);
        $this->service->handleFailure($exception);
    }

    public function testHandleFailure_ShouldClearTokens_WhenUnauthorized(): void
    {
        // Given
        $exception = new \Exception('Unauthorized', Response::HTTP_UNAUTHORIZED);

        // Then
        $this->connectionServiceMock->expects($this->once())->method('clearTokens');

        // When & Then
        $this->expectException(\Exception::class);
        $this->service->handleFailure($exception);
    }

    public function testStoreCurrentTrack_ShouldThrowException_WhenApiReturnsError(): void
    {
        // Given
        $this->connectionServiceMock->method('connect')->willReturn(true);
        $apiError = new ApiError(message: 'API Error');
        $apiListening = new ApiListening(error: $apiError);
        $this->denormalizerMock->method('denormalize')->willReturn($apiListening);
        $this->spotifyApiMock->method('getMyCurrentTrack')->willReturn([]);

        // Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API Error');

        // When
        $this->service->storeCurrentTrack();
    }
}