<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Api;

class ApiPlaylist
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
