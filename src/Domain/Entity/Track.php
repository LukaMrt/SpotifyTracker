<?php

namespace App\Domain\Entity;

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

    public function getArtists(): array
    {
        return $this->artists;
    }
}