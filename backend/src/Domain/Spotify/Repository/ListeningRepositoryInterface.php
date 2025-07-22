<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Repository;

use App\Domain\Spotify\Entity\Listening;
use App\Domain\Spotify\Entity\SpotifyId;

interface ListeningRepositoryInterface
{
    public function save(Listening $listening): void;

    /**
     * @return Listening[]
     */
    public function findByDateRange(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?SpotifyId $playlistId = null,
        ?SpotifyId $artistId = null,
        ?SpotifyId $trackId = null
    ): array;

    /**
     * @param SpotifyId[] $artistIds
     * @return array<string, int> Array with keys as names and values as listening counts
     */
    public function getArtistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $artistIds
    ): array;

    /**
     * @param SpotifyId[] $trackIds
     * @return array<string, int> Array with keys as names and values as listening counts
     */
    public function getTrackStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $trackIds
    ): array;

    /**
     * @param SpotifyId[] $playlistIds
     * @return array<string, int> Array with keys as names and values as listening counts
     */
    public function getPlaylistStats(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $playlistIds
    ): array;
}
