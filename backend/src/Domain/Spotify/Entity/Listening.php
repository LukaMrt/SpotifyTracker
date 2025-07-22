<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Entity;

class Listening
{
    public function __construct(
        protected readonly \DateTimeImmutable $dateTime,
        protected readonly Track $track,
        protected readonly ?Playlist $playlist,
    ) {
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getTrack(): Track
    {
        return $this->track;
    }

    public function getPlaylist(): ?Playlist
    {
        return $this->playlist;
    }
}
