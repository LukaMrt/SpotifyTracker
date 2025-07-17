<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250206102402CreateDatabase extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE artist (
                id BINARY(22) NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            );

            CREATE TABLE playlist (
                id BINARY(22) NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            );

            CREATE TABLE track (
                id BINARY(22) NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            );

            CREATE TABLE author (
                track_id BINARY(22) NOT NULL,
                artist_id BINARY(22) NOT NULL,
                PRIMARY KEY(track_id, artist_id)
            );

            CREATE TABLE listening (
                dateTime DATETIME NOT NULL,
                track_id BINARY(22) NOT NULL,
                playlist_id BINARY(22),
                PRIMARY KEY(dateTime)
            );

            ALTER TABLE author
                ADD CONSTRAINT fk_author_track FOREIGN KEY (track_id) REFERENCES track (id),
                ADD CONSTRAINT fk_author_artist FOREIGN KEY (artist_id) REFERENCES artist (id);

            ALTER TABLE listening
                ADD CONSTRAINT fk_listening_track FOREIGN KEY (track_id) REFERENCES track (id),
                ADD CONSTRAINT fk_listening_playlist FOREIGN KEY (playlist_id) REFERENCES playlist (id);
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
            DROP TABLE listening;
            DROP TABLE author;
            DROP TABLE track;
            DROP TABLE playlist;
            DROP TABLE artist;
        SQL);
    }
}
