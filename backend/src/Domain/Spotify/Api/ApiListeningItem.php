<?php

declare(strict_types=1);

namespace App\Domain\Spotify\Api;

use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Entity\Track;
use App\Lib\MapCollection;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[Map(target: Track::class)]
class ApiListeningItem
{
    /**
     * @param ApiArtist[] $artists An array of artists associated with the item.
     */
    public function __construct(
        #[Map(target: 'id', transform: [SpotifyId::class, 'from'])]
        public readonly string $id,
        #[Map(target: 'name')]
        public readonly string $name,
        #[Map(target: 'artists', transform: MapCollection::class)]
        #[SerializedName('artists')]
        public readonly array $artists,
    ) {
    }
}
