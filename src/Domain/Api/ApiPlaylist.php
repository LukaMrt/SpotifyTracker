<?php

namespace App\Domain\Api;

class ApiPlaylist
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
