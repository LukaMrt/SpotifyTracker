<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Domain\Spotify\Entity\Artist;
use App\Infrastructure\Repository\ArtistMysqlRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[Group('integration')]
final class ArtistMysqlRepositoryTest extends KernelTestCase
{
    private ArtistMysqlRepository $artistRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get required dependencies from container
        $connection = self::getContainer()->get(Connection::class);
        $serializer = self::getContainer()->get(SerializerInterface::class);
        
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(SerializerInterface::class, $serializer);
        
        // Create repository instance manually
        $this->artistRepository = new ArtistMysqlRepository($connection, $serializer);
    }

    public function testFindAllWithListenings_ShouldReturn14Artists(): void
    {
        // When
        $artists = $this->artistRepository->findAllWithListenings();

        // Then - Based on fixtures: 14 artists, all have tracks/listenings
        $this->assertCount(14, $artists);
    }

    public function testFindByNameLike_WithExactMatch_ShouldReturnOneArtist(): void
    {
        // Given - "Carston" exists in fixtures
        $searchName = 'Carston';

        // When
        $artists = $this->artistRepository->findByNameLike($searchName);

        // Then
        $this->assertCount(1, $artists);
    }

    public function testFindByNameLike_WithPartialMatch_ShouldReturnMatchingArtists(): void
    {
        // Given - "ea" should match "Andrea Toscano", "Rea Garvey" and "The Beatles"
        $partialName = 'ea';

        // When
        $artists = $this->artistRepository->findByNameLike($partialName);

        // Then
        $this->assertCount(3, $artists);
    }

    public function testFindByNameLike_WithNonExistentName_ShouldReturnEmptyArray(): void
    {
        // Given
        $nonExistentName = 'NonExistentArtistName123456';

        // When
        $artists = $this->artistRepository->findByNameLike($nonExistentName);

        // Then
        $this->assertCount(0, $artists);
    }

    public function testFindWithListeningsBetween_WithValidRange_ShouldReturnAllArtists(): void
    {
        // Given - Range covering all fixtures (last month)
        $startDate = new \DateTimeImmutable('-2 months');
        $endDate = new \DateTimeImmutable('+1 day');

        // When
        $artists = $this->artistRepository->findWithListeningsBetween($startDate, $endDate);

        // Then - All 14 artists should have listenings in this range
        $this->assertCount(14, $artists);
    }

    public function testFindWithListeningsBetween_WithFutureDates_ShouldReturnZeroArtists(): void
    {
        // Given - Future date range where no listenings exist
        $startDate = new \DateTimeImmutable('2030-01-01');
        $endDate = new \DateTimeImmutable('2030-12-31');

        // When
        $artists = $this->artistRepository->findWithListeningsBetween($startDate, $endDate);

        // Then
        $this->assertCount(0, $artists);
    }

    public function testFindWithListeningsBetween_WithInvertedDates_ShouldReturnZeroArtists(): void
    {
        // Given - Start date after end date
        $startDate = new \DateTimeImmutable('2025-12-31');
        $endDate = new \DateTimeImmutable('2025-01-01');

        // When
        $artists = $this->artistRepository->findWithListeningsBetween($startDate, $endDate);

        // Then
        $this->assertCount(0, $artists);
    }

    public function testAllMethods_ShouldReturnArtistsSortedByName(): void
    {
        // Test that all methods return artists sorted by name
        $methods = [
            fn(): array => $this->artistRepository->findAllWithListenings(),
            fn(): array => $this->artistRepository->findByNameLike('a'), // Should match several artists
            fn(): array => $this->artistRepository->findWithListeningsBetween(
                new \DateTimeImmutable('-2 months'),
                new \DateTimeImmutable('+1 day')
            ),
        ];

        foreach ($methods as $method) {
            // When
            $artists = $method();

            // Then
            if (count($artists) > 1) {
                $names = array_map(fn(Artist $artist): string => $artist->getName(), $artists);
                $sortedNames = $names;
                sort($sortedNames, SORT_STRING | SORT_FLAG_CASE);
                
                $this->assertSame($sortedNames, $names, 'Artists should be sorted by name (case-insensitive)');
            }
        }
    }

    public function testFindByNameLike_WithCaseInsensitive_ShouldReturnMatch(): void
    {
        // Given - Test case insensitive search
        $searchName = 'CARSTON'; // Uppercase version of "Carston"

        // When
        $artists = $this->artistRepository->findByNameLike($searchName);

        // Then
        $this->assertCount(1, $artists);
        $this->assertSame('Carston', $artists[0]->getName());
    }
}