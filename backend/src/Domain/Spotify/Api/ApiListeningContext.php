<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Api;

class ApiListeningContext
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $uri = null,
    ) {
    }
}
