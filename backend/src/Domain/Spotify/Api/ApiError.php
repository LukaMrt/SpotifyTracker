<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Api;

class ApiError
{
    public function __construct(
        public readonly ?string $message = null,
    ) {
    }
}
