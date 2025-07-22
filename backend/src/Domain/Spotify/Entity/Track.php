<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Entity;

class Track
{
    /**
     * @param Artist[] $artists
     */
    public function __construct(
        protected readonly SpotifyId $id,
        protected readonly string $name,
        protected readonly array $artists,
    ) {
    }

    public function getId(): SpotifyId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Artist[]
     */
    public function getArtists(): array
    {
        return $this->artists;
    }
}
