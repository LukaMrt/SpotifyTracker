<?php

namespace App\Domain\Api;

class ApiArtist
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
