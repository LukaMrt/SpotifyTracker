<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Api;

use App\Domain\Spotify\Entity\Playlist;
use App\Domain\Spotify\Entity\SpotifyId;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Playlist::class)]
class ApiPlaylist
{
    public function __construct(
        #[Map(target: 'id', transform: [SpotifyId::class, 'from'])]
        public readonly string $id,
        #[Map(target: 'name')]
        public readonly string $name,
    ) {
    }
}
