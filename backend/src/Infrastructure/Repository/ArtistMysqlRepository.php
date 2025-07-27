<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Repository\ArtistRepositoryInterface;
use App\Infrastructure\Serializer\SpotifyIdDenormalizer;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\SerializerInterface;

class ArtistMysqlRepository implements ArtistRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[\Override]
    public function findAllWithListenings(): array
    {
        $result = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT JSON_OBJECT(
                    'id', HEX(artist.id),
                    'name', artist.name
                ) AS artist
                FROM artist
                ORDER BY artist.name COLLATE utf8mb4_unicode_ci ASC
            SQL
        );

        return $this->deserializeArtists($result);
    }

    #[\Override]
    public function findByNameLike(string $name): array
    {
        $result = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT JSON_OBJECT(
                    'id', HEX(artist.id),
                    'name', artist.name
                ) AS artist
                FROM artist
                WHERE artist.name LIKE :name COLLATE utf8mb4_unicode_ci
                ORDER BY artist.name COLLATE utf8mb4_unicode_ci ASC
            SQL,
            ['name' => '%' . $name . '%']
        );

        return $this->deserializeArtists($result);
    }

    #[\Override]
    public function findWithListeningsBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $result = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT JSON_OBJECT(
                    'id', HEX(artist.id),
                    'name', artist.name
                ) AS artist
                FROM artist
                INNER JOIN author ON artist.id = author.artist_id
                INNER JOIN track ON author.track_id = track.id
                INNER JOIN listening ON track.id = listening.track_id
                WHERE listening.dateTime BETWEEN :startDate AND :endDate
                GROUP BY artist.id, artist.name
                ORDER BY artist.name COLLATE utf8mb4_unicode_ci ASC
            SQL,
            [
                'startDate' => $startDate->format('Y-m-d H:i:s'),
                'endDate' => $endDate->format('Y-m-d H:i:s'),
            ]
        );

        return $this->deserializeArtists($result);
    }

    /**
     * @param list<array<string, mixed>> $result
     * @return Artist[]
     */
    private function deserializeArtists(array $result): array
    {
        return array_map(
            fn (array $row): Artist => $this->serializer->deserialize(
                $row['artist'],
                Artist::class,
                'json',
                [SpotifyIdDenormalizer::FROM_MYSQL_FLAG => true]
            ),
            $result
        );
    }
}