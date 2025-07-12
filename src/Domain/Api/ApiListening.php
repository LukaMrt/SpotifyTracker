<?php

namespace App\Domain\Api;

class ApiListening
{
    public function __construct(
        public readonly ?ApiError $error = null,
        public readonly ?bool $is_playing = false,
        public readonly ?ApiListeningContext $context = null,
        public readonly ?ApiListeningItem $item = null,
    ) {
    }
}
