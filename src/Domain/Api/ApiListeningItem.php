<?php

namespace App\Domain\Api;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ApiListeningItem
{
    /**
     * @param ApiArtist[] $artists An array of artists associated with the item.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        #[SerializedName('artists')]
        public readonly array $artists,
    ) {
    }
}
