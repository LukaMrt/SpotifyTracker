<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Entity;

class Artist
{
    public function __construct(
        protected readonly SpotifyId $id,
        protected readonly string $name,
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
}
