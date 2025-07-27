<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Entity;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class Track
{
    /**
     * @param Artist[] $artists
     */
    public function __construct(
        protected readonly SpotifyId $id,
        protected readonly string $name,
        #[SerializedName('artists')]
        #[Assert\Count(min: 1, minMessage: 'Track must have at least one artist')]
        protected readonly array $artists,
    ) {
    }

    public function getId(): SpotifyId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Artist[]
     */
    public function getArtists(): array
    {
        return $this->artists;
    }
}
