<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Artist;
use App\Domain\Entity\Listening;
use App\Domain\Entity\Playlist;
use App\Domain\Entity\SpotifyId;
use App\Domain\Entity\Track;
use App\Domain\Repository\ListeningRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;

class ListeningMysqlRepository implements ListeningRepositoryInterface
{
    public function __construct(
        protected readonly Connection $connection,
        protected readonly LoggerInterface $logger,
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
        if ($playlist === null) {
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
}