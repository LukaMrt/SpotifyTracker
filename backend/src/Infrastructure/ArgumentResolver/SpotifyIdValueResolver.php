<?php

declare(strict_types=1);

namespace App\Infrastructure\ArgumentResolver;

use App\Domain\Spotify\Entity\SpotifyId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SpotifyIdValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<SpotifyId>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== SpotifyId::class) {
            return [];
        }

        $parameterName = $this->getParameterName($argument);
        $value = $request->query->get($parameterName);

        if ($value === null) {
            if ($argument->isNullable()) {
                return null;
            }

            throw new InvalidRequestException(sprintf('Required parameter "%s" is missing', $parameterName));
        }

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidRequestException(sprintf('Invalid SpotifyId format for parameter "%s": %s', $parameterName, $value));
        }

        yield new SpotifyId($value);
    }

    private function getParameterName(ArgumentMetadata $argument): string
    {
        // Convert camelCase to snake_case for query parameters
        return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $argument->getName()));
    }
}
