<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Spotify\Entity\Artist;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Artist::class)]
final readonly class ArtistDto
{
    public function __construct(
        #[Map(source: 'id.id')]
        public string $id,
        
        #[Map(source: 'name')]
        public string $name,
    ) {
    }
}