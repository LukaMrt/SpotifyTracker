<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\ListeningCount;
use App\Domain\Spotify\Entity\Playlist;
use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Entity\Track;
use App\Domain\Spotify\Repository\ListeningRepositoryInterface;
use App\Infrastructure\Serializer\SpotifyIdDenormalizer;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ListeningMysqlRepository implements ListeningRepositoryInterface
{
    public const int MAX_STATS_LIMIT = 100;

    public function __construct(
        protected readonly Connection $connection,
        protected readonly LoggerInterface $logger,
        protected readonly SerializerInterface $serializer,
    ) {
    }

    #[\Override]
    public function save(Listening $listening): void
    {
        try {
            $this->connection->beginTransaction();

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
                    'track_id'    => ParameterType::STRING,
                    'playlist_id' => ParameterType::STRING,
                ]
            );

            $this->connection->commit();
        } catch (\Throwable $throwable) {
            $this->connection->rollBack();
            throw $throwable;
        }
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
                'id'   => ParameterType::STRING,
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
                'id'   => ParameterType::STRING,
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
                'track_id'  => ParameterType::STRING,
                'artist_id' => ParameterType::STRING,
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
                'id'   => ParameterType::STRING,
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

        if ($playlistId instanceof SpotifyId) {
            $where .= ' AND playlist.id = :playlistId';
            $params['playlistId'] = $playlistId;
            $types['playlistId'] = ParameterType::BINARY;
        }

        if ($artistId instanceof SpotifyId) {
            $where .= ' AND artist.id = :artistId';
            $params['artistId'] = $artistId;
            $types['artistId'] = ParameterType::BINARY;
        }

        if ($trackId instanceof SpotifyId) {
            $where .= ' AND track.id = :trackId';
            $params['trackId'] = $trackId;
            $types['trackId'] = ParameterType::BINARY;
        }

        $result = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT JSON_OBJECT(
                    'dateTime', listening.dateTime,
                    'track', JSON_OBJECT(
                        'id', HEX(track.id),
                        'name', track.name,
                        'artists', JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id',  HEX(artist.id),
                                'name', artist.name
                            )
                        )
                    ),
                    'playlist', IF(
                        playlist.id IS NULL,
                        NULL,
                        JSON_OBJECT(
                            'id', HEX(playlist.id),
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
            fn ($row): mixed => $this->serializer->deserialize(
                $row['listening'],
                Listening::class,
                'json',
                [SpotifyIdDenormalizer::FROM_MYSQL_FLAG => true]
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
        $stats = $this->getStats(
            'artist',
            'listening
            INNER JOIN track ON listening.track_id = track.id
            INNER JOIN author ON track.id = author.track_id
            INNER JOIN artist ON author.artist_id = artist.id',
            $startDate,
            $endDate,
            $artistIds
        );

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->getName()] = $stat->getCount();
        }

        return $result;
    }

    #[\Override]
    public function getTrackStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $trackIds
    ): array {
        $stats = $this->getStats(
            'track',
            'listening
            INNER JOIN track ON listening.track_id = track.id',
            $startDate,
            $endDate,
            $trackIds
        );

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->getName()] = $stat->getCount();
        }

        return $result;
    }

    #[\Override]
    public function getPlaylistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $playlistIds
    ): array {
        $stats = $this->getStats(
            'playlist',
            'listening
            INNER JOIN playlist ON listening.playlist_id = playlist.id',
            $startDate,
            $endDate,
            $playlistIds
        );

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->getName()] = $stat->getCount();
        }

        return $result;
    }

    /**
     * @param SpotifyId[] $entityIds
     * @return ListeningCount[]
     * @throws Exception
     * @throws ExceptionInterface
     */
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
            'limit' => self::MAX_STATS_LIMIT,
        ];
        $types = [
            'startDate' => ParameterType::STRING,
            'endDate' => ParameterType::STRING,
            'limit' => ParameterType::INTEGER,
        ];

        if ($entityIds !== []) {
            $where .= sprintf(' AND %s.id IN (:entityIds)', $entityName);
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
                LIMIT :limit
        SQL,
            $params,
            $types
        );

        return array_map(
            fn (array $row): mixed => $this->serializer->deserialize(
                $row["stat"],
                ListeningCount::class,
                'json',
            ),
            $result,
        );
    }
}
