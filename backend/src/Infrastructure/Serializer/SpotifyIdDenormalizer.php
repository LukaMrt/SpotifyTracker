<?php

declare(strict_types=1);

namespace App\Infrastructure\Serializer;

use App\Domain\Spotify\Entity\SpotifyId;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SpotifyIdDenormalizer implements DenormalizerInterface
{
    public const string FROM_MYSQL_FLAG = 'from_mysql';
    
    /**
     * @param string $data
     * @param array<string, mixed> $context
     */
    // @phpstan-ignore-next-line
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): SpotifyId
    {
        if ($context[self::FROM_MYSQL_FLAG] === true) {
            $data = hex2bin($data);
        }

        if ($data === false) {
            return new SpotifyId('');
        }
        
        return new SpotifyId($data);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $context
     */
    // @phpstan-ignore-next-line
    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === SpotifyId::class;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [SpotifyId::class => true];
    }
}
