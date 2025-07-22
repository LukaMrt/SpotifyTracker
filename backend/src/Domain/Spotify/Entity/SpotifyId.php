<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SpotifyId implements \Stringable
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex('/^[a-zA-Z0-9]{22}$/')]
        public readonly string $id,
    ) {
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(object $other): bool
    {
        return $other instanceof self && $other->id === $this->id;
    }
}
