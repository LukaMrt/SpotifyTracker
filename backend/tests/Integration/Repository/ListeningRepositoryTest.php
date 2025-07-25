<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
final class ListeningRepositoryTest extends KernelTestCase
{
    private ListeningRepositoryInterface $repository;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        /** @var ListeningRepositoryInterface $repository */
        $repository = $container->get(ListeningRepositoryInterface::class);
        $this->repository = $repository;
    }

    public function testFindByDateRange_ShouldReturnListenings_WhenDataExists(): void
    {
        // Given - Use current time range (fixtures are between now-1month and yesterday)
        $now = new \DateTimeImmutable();
        $startDate = $now->modify('-2 months');
        $endDate = $now;

        // When
        $listenings = $this->repository->findByDateRange($startDate, $endDate);

        // Then
        $this->assertCount(100, $listenings);
        $this->assertNotEmpty($listenings[0]->getTrack()->getName());
        $this->assertNotEmpty($listenings[0]->getTrack()->getArtists());
    }

    public function testGetArtistStats_ShouldReturnStats_WhenDataExists(): void
    {
        // Given - Use current time range (fixtures are between now-1month and yesterday)
        $now = new \DateTimeImmutable();
        $startDate = $now->modify('-2 months');
        $endDate = $now;

        // When
        $stats = $this->repository->getArtistStats($startDate, $endDate, []);

        // Then
        $this->assertNotEmpty($stats);
        
        // Check that we have artist names as keys and counts as values
        foreach ($stats as $artistName => $count) {
            $this->assertIsString($artistName);
            $this->assertIsInt($count);
            $this->assertGreaterThan(0, $count);
        }
    }

    public function testGetTrackStats_ShouldReturnStats_WhenDataExists(): void
    {
        // Given - Use current time range (fixtures are between now-1month and yesterday)
        $now = new \DateTimeImmutable();
        $startDate = $now->modify('-2 months');
        $endDate = $now;

        // When
        $stats = $this->repository->getTrackStats($startDate, $endDate, []);

        // Then
        $this->assertNotEmpty($stats);
        
        // Check that we have track names as keys and counts as values
        foreach ($stats as $trackName => $count) {
            $this->assertIsString($trackName);
            $this->assertIsInt($count);
            $this->assertGreaterThan(0, $count);
        }
    }

    public function testGetPlaylistStats_ShouldReturnStats_WhenDataExists(): void
    {
        // Given - Use current time range (fixtures are between now-1month and yesterday)
        $now = new \DateTimeImmutable();
        $startDate = $now->modify('-2 months');
        $endDate = $now;

        // When
        $stats = $this->repository->getPlaylistStats($startDate, $endDate, []);

        // Then
        $this->assertNotEmpty($stats);
        
        // Check that we have playlist names as keys and counts as values
        foreach ($stats as $playlistName => $count) {
            $this->assertIsString($playlistName);
            $this->assertIsInt($count);
            $this->assertGreaterThan(0, $count);
        }
    }
}