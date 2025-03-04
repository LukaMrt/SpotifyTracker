<?php

namespace App\Domain\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SpotifyId
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex('/^[a-zA-Z0-9]{22}$/')]
        public readonly string $id,
    ) {
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(object $other): bool
    {
        return $other instanceof self && $other->id === $this->id;
    }
}
