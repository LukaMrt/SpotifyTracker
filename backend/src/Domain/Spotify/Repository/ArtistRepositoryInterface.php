<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Repository;

use App\Domain\Spotify\Entity\Artist;

interface ArtistRepositoryInterface
{
    /**
     * Find all artists that have at least one listening record
     * 
     * @return Artist[]
     */
    public function findAllWithListenings(): array;

    /**
     * Find artists by name (case-insensitive partial match)
     * Only returns artists that have listening records
     * 
     * @return Artist[]
     */
    public function findByNameLike(string $name): array;

    /**
     * Find artists that have listening records within the specified date range
     * 
     * @return Artist[]
     */
    public function findWithListeningsBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;
}