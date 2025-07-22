<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Api;

class ApiListening
{
    public function __construct(
        public readonly ?ApiError $error = null,
        public readonly ?bool $is_playing = false,
        public readonly ?ApiListeningContext $context = null,
        public readonly ?ApiListeningItem $item = null,
        public readonly ?ApiPlaylist $playlist = null,
    ) {
    }

    public function withPlaylist(ApiPlaylist $playlist): self
    {
        return new self(
            $this->error,
            $this->is_playing,
            $this->context,
            $this->item,
            $playlist
        );
    }
}
