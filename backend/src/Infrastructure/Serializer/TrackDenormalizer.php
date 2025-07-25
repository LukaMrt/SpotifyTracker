<?php

declare(strict_types=1);

namespace App\Infrastructure\Serializer;

use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Entity\Track;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TrackDenormalizer implements DenormalizerInterface
{
    public function __construct(
        protected readonly SpotifyIdDenormalizer $spotifyIdDenormalizer,
    ) {
    }

    /**
     * @param array{
     *     'id': string,
     *     'name': string,
     *     'artists': array<array{
     *         'id': string,
     *         'name': string
     *     }>
     * } $data
     * @param array<string, mixed> $context
     */
    // @phpstan-ignore-next-line
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Track
    {
        return new Track(
            id: $this->spotifyIdDenormalizer->denormalize($data['id'], SpotifyId::class, $format, $context),
            name: $data['name'],
            artists: array_map(
                fn(array $artist): Artist => new Artist(
                    id: $this->spotifyIdDenormalizer->denormalize($artist['id'], SpotifyId::class, $format, $context),
                    name: $artist['name']
                ),
                $data['artists'],
            )
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $context
     */
    // @phpstan-ignore-next-line
    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Track::class;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Track::class => true];
    }
}
