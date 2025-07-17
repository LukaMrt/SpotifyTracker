<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Artist;
use App\Domain\Entity\Listening;
use App\Domain\Entity\ListeningCount;
use App\Domain\Entity\Playlist;
use App\Domain\Entity\SpotifyId;
use App\Domain\Entity\Track;
use App\Domain\Repository\ListeningRepositoryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ListeningMysqlRepository implements ListeningRepositoryInterface
{
    public function __construct(
        protected readonly Connection $connection,
        protected readonly LoggerInterface $logger,
        protected readonly SerializerInterface $serializer,
    ) {
    }

    #[\Override]
    public function save(Listening $listening): void
    {
        $this->saveTrack($listening->getTrack());
        $this->savePlaylist($listening->getPlaylist());

        $this->logger->info('Saving listening', ['dateTime'  => $listening->getDateTime()->format('Y-m-d H:i:s')]);
        $this->connection->executeStatement(
            <<<SQL
                INSERT INTO listening (dateTime, track_id, playlist_id)
                VALUES (:dateTime, :track_id, :playlist_id)
        SQL,
            [
                'dateTime'    => $listening->getDateTime()->format('Y-m-d H:i:s'),
                'track_id'    => $listening->getTrack()->getId(),
                'playlist_id' => $listening->getPlaylist()?->getId(),
            ],
            [
                'dateTime'    => ParameterType::STRING,
                'track_id'    => ParameterType::BINARY,
                'playlist_id' => ParameterType::BINARY,
            ]
        );
    }

    protected function saveTrack(Track $track): void
    {
        $this->logger->info('Saving track', ['id' => $track->getId()->id, 'name' => $track->getName()]);
        $this->connection->executeStatement(
            <<<SQL
                INSERT IGNORE INTO track (id, name)
                VALUES (:id, :name)
        SQL,
            [
                'id'   => $track->getId(),
                'name' => $track->getName(),
            ],
            [
                'id'   => ParameterType::BINARY,
                'name' => ParameterType::STRING,
            ]
        );

        foreach ($track->getArtists() as $artist) {
            $this->saveArtist($artist, $track->getId());
        }
    }

    protected function saveArtist(Artist $artist, SpotifyId $trackId): void
    {
        $this->logger->info('Saving artist', ['id' => $artist->getId()->id, 'name' => $artist->getName()]);
        $this->connection->executeStatement(
            <<<SQL
                INSERT IGNORE INTO artist (id, name)
                VALUES (:id, :name)
            SQL,
            [
                'id'   => $artist->getId(),
                'name' => $artist->getName(),
            ],
            [
                'id'   => ParameterType::BINARY,
                'name' => ParameterType::STRING,
            ]
        );

        $this->connection->executeStatement(
            <<<SQL
                    INSERT IGNORE INTO author (track_id, artist_id)
                    VALUES (:track_id, :artist_id)
            SQL,
            [
                'track_id'  => $trackId,
                'artist_id' => $artist->getId(),
            ],
            [
                'track_id'  => ParameterType::BINARY,
                'artist_id' => ParameterType::BINARY,
            ]
        );
    }

    protected function savePlaylist(?Playlist $playlist): void
    {
        if (!$playlist instanceof Playlist) {
            return;
        }

        $this->logger->info('Saving playlist', ['id' => $playlist->getId()->id, 'name' => $playlist->getName()]);
        $this->connection->executeStatement(
            <<<SQL
                INSERT IGNORE INTO playlist (id, name)
                VALUES (:id, :name)
            SQL,
            [
                'id'   => $playlist->getId(),
                'name' => $playlist->getName(),
            ],
            [
                'id'   => ParameterType::BINARY,
                'name' => ParameterType::STRING,
            ]
        );
    }

    #[\Override]
    public function findByDateRange(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?SpotifyId $playlistId = null,
        ?SpotifyId $artistId = null,
        ?SpotifyId $trackId = null
    ): array {
        $where = '';

        $params = [
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ];
        $types = [
            'startDate' => ParameterType::STRING,
            'endDate' => ParameterType::STRING,
        ];

        if ($playlistId !== null) {
            $where .= ' AND playlist.id = :playlistId';
            $params['playlistId'] = $playlistId;
            $types['playlistId'] = ParameterType::BINARY;
        }

        if ($artistId !== null) {
            $where .= ' AND artist.id = :artistId';
            $params['artistId'] = $artistId;
            $types['artistId'] = ParameterType::BINARY;
        }

        if ($trackId !== null) {
            $where .= ' AND track.id = :trackId';
            $params['trackId'] = $trackId;
            $types['trackId'] = ParameterType::BINARY;
        }

        $result = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT JSON_OBJECT(
                    'dateTime', listening.dateTime,
                    'track', JSON_OBJECT(
                        'id', track.id,
                        'name', track.name,
                        'artists', JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', artist.id,
                                'name', artist.name
                            )
                        )
                    ),
                    'playlist', IF(
                        playlist.id IS NULL,
                        NULL,
                        JSON_OBJECT(
                            'id', playlist.id,
                            'name', playlist.name
                        )
                    )
                ) AS listening
                FROM listening
                INNER JOIN track ON listening.track_id = track.id
                LEFT JOIN playlist ON listening.playlist_id = playlist.id
                LEFT JOIN author ON track.id = author.track_id
                LEFT JOIN artist ON author.artist_id = artist.id
                WHERE listening.dateTime BETWEEN :startDate AND :endDate
                {$where}
                GROUP BY listening.dateTime, track.id, playlist.id
                ORDER BY listening.dateTime DESC
        SQL,
            $params,
            $types
        );

        return array_map(
            fn ($row) => $this->serializer->deserialize(
                $row['listening'],
                Listening::class,
                'json',
            ),
            $result,
        );
    }

    #[\Override]
    public function getArtistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $artistIds
    ): array {
        return $this->getStats(
            'artist',
            'listening
            INNER JOIN track ON listening.track_id = track.id
            INNER JOIN author ON track.id = author.track_id
            INNER JOIN artist ON author.artist_id = artist.id',
            $startDate,
            $endDate,
            $artistIds
        );
    }

    #[\Override]
    public function getTrackStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $trackIds
    ): array {
        return $this->getStats(
            'track',
            'listening
            INNER JOIN track ON listening.track_id = track.id',
            $startDate,
            $endDate,
            $trackIds
        );
    }

    #[\Override]
    public function getPlaylistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $playlistIds
    ): array {
        return $this->getStats(
            'playlist',
            'listening
            INNER JOIN playlist ON listening.playlist_id = playlist.id',
            $startDate,
            $endDate,
            $playlistIds
        );
    }

    private function getStats(
        string $entityName,
        string $fromClause,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $entityIds
    ): array {
        $where = '';

        $params = [
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ];
        $types = [
            'startDate' => ParameterType::STRING,
            'endDate' => ParameterType::STRING,
        ];

        if (!empty($entityIds)) {
            $where .= " AND {$entityName}.id IN (:entityIds)";
            $params["entityIds"] = $entityIds;
            $types["entityIds"] = ArrayParameterType::BINARY;
        }

        $result = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT JSON_OBJECT(
                    'name', {$entityName}.name,
                    'count', COUNT(*)
                ) AS stat
                FROM {$fromClause}
                WHERE listening.dateTime BETWEEN :startDate AND :endDate
                {$where}
                GROUP BY {$entityName}.id, {$entityName}.name
                ORDER BY COUNT(*) DESC
        SQL,
            $params,
            $types
        );

        return array_map(
            fn (array $row) => $this->serializer->deserialize(
                $row["stat"],
                ListeningCount::class,
                'json',
            ),
            $result,
        );
    }
}
