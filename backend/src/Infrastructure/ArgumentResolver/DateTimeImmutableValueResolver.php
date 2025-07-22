<?php

declare(strict_types=1);

namespace App\Infrastructure\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DateTimeImmutableValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<\DateTimeImmutable>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== \DateTimeImmutable::class) {
            return [];
        }

        $parameterName = $this->getParameterName($argument);
        $value = $request->query->get($parameterName);

        if ($value === null || $value === '') {
            if ($argument->isNullable()) {
                return null;
            }

            throw new InvalidRequestException(sprintf('Required parameter %s is missing', $parameterName));
        }

        try {
            yield new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new InvalidRequestException(sprintf('Invalid date format for parameter "%s": %s', $parameterName, $value));
        }
    }

    private function getParameterName(ArgumentMetadata $argument): string
    {
        // Convert camelCase to snake_case for query parameters
        return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $argument->getName()));
    }
}
