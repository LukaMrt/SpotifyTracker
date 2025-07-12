<?php

namespace App\Domain\Api;

use Symfony\Component\Validator\Constraints as Assert;

class SpotifyTokens
{
    public function __construct(
        #[Assert\NotBlank]
        protected readonly string $accessToken,

        #[Assert\NotBlank]
        protected readonly string $refreshToken,
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function equals(object $other): bool
    {
        return $other instanceof self 
            && $other->accessToken === $this->accessToken
            && $other->refreshToken === $this->refreshToken;
    }
}
