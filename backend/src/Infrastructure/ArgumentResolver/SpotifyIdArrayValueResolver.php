<?php

declare(strict_types=1);

namespace App\Infrastructure\ArgumentResolver;

use App\Domain\Spotify\Entity\SpotifyId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SpotifyIdArrayValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<SpotifyId[]>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // Only handle arrays of SpotifyId
        if ($argument->getType() !== 'array' || !str_contains($argument->getName(), 'Ids')) {
            return [];
        }

        $parameterName = $this->getParameterName($argument);
        $value = $request->query->get($parameterName);

        if ($value === null) {
            return [];
        }

        $stringIds = $this->parseArrayParameter($value);
        yield array_map(fn(string $id): SpotifyId => new SpotifyId($id), $stringIds);
    }

    private function getParameterName(ArgumentMetadata $argument): string
    {
        // Convert camelCase to snake_case for query parameters
        return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $argument->getName()));
    }

    /**
     * @return string[]
     */
    private function parseArrayParameter(string $parameter): array
    {
        // Support both comma-separated values and JSON arrays
        if (str_starts_with(trim($parameter), '[')) {
            $decoded = json_decode($parameter, true);
            if (!is_array($decoded)) {
                return [];
            }

            // Ensure we return an array of strings
            return array_filter($decoded, fn($value): bool => is_string($value));
        }

        return array_filter(array_map('trim', explode(',', $parameter)), fn(string $value): bool => $value !== '');
    }
}
