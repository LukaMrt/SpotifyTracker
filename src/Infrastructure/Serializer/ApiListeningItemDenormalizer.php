<?php

namespace App\Infrastructure\Serializer;

use App\Domain\Api\ApiArtist;
use App\Domain\Api\ApiListeningItem;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ApiListeningItemDenormalizer implements DenormalizerInterface
{
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
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ApiListeningItem
    {
        $artists = array_map(
            static fn (array $artistData) => new ApiArtist(
                id: $artistData['id'],
                name: $artistData['name']
            ),
            $data['artists']
        );

        return new ApiListeningItem(
            id: $data['id'],
            name: $data['name'],
            artists: $artists
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
        return $type === ApiListeningItem::class;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [ApiListeningItem::class => true];
    }
}