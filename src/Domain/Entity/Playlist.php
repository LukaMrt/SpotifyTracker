<?php

namespace App\Domain\Entity;

class Playlist
{
    public function __construct(
        protected SpotifyId $id,
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