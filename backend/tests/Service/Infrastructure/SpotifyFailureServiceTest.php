<?php

declare(strict_types=1);

namespace App\Tests\Service\Infrastructure;

use App\Service\Infrastructure\SpotifyFailureService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Cache\CacheInterface;

#[Group('unit')]
final class SpotifyFailureServiceTest extends TestCase
{
    private SpotifyFailureService $service;
    
    private CacheInterface&MockObject $cacheMock;
    
    private LoggerInterface&MockObject $loggerMock;
    
    private MailerInterface&MockObject $mailerMock;
    
    private Session&MockObject $sessionMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->mailerMock = $this->createMock(MailerInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $adminEmail = 'admin@test.com';

        $this->service = new SpotifyFailureService(
            $this->cacheMock,
            $this->loggerMock,
            $this->mailerMock,
            $this->sessionMock,
            $adminEmail
        );
    }

    public function testHandleFailure_ShouldLogConnectionFailure_WhenNotConnected(): void
    {
        // Given
        $this->cacheMock->method('get')->willReturnOnConsecutiveCalls(0, 1);
        $this->cacheMock->method('delete')->willReturn(true);
        $this->sessionMock->method('getAuthorizeUrl')->willReturn('http://auth.url');

        // Then
        $this->loggerMock->expects($this->atLeastOnce())->method('info');
        $this->loggerMock->expects($this->once())->method('error');

        // When
        $this->service->handleFailure(false);
    }

    public function testHandleFailure_ShouldSendNotification_WhenFailCountHigh(): void
    {
        // Given
        $this->cacheMock->method('get')->willReturnOnConsecutiveCalls(
            2, 3, 0, time() - 3600
        );
        $this->cacheMock->method('delete')->willReturn(true);

        // Then
        $this->mailerMock->expects($this->once())->method('send')
            ->with(self::isInstanceOf(Email::class));

        // When
        $this->service->handleFailure(true);
    }

    public function testClearFailCount_ShouldDeleteFromCache(): void
    {
        // Then
        $this->cacheMock->expects($this->once())->method('delete')->with('spotify_fail_count');

        // When
        $this->service->clearFailCount();
    }

    public function testLogConnectionFailure_ShouldCallLogger(): void
    {
        // Given
        $this->sessionMock->method('getAuthorizeUrl')->willReturn('http://test.url');

        // Then
        $this->loggerMock->expects($this->once())->method('info');
        $this->loggerMock->expects($this->once())->method('error');

        // When
        $this->service->logConnectionFailure();
    }
}