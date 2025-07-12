<?php

namespace App\Domain\Api;

class ApiError
{
    public function __construct(
        public readonly ?string $message = null,
    ) {
    }
}
