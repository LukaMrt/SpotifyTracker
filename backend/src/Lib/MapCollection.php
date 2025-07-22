<?php

declare(strict_types=1);

namespace App\Lib;

use Symfony\Component\ObjectMapper\Exception\MappingException;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

/**
 * @template T of object
 *
 * @implements TransformCallableInterface<object, T>
 */
// @phpstan-ignore
class MapCollection implements TransformCallableInterface
{
    public function __construct(
        private readonly ObjectMapperInterface $objectMapper = new ObjectMapper()
    ) {
    }

    public function __invoke(mixed $value, object $source, ?object $target): array
    {
        if (!is_iterable($value)) {
            throw new MappingException(sprintf('The MapCollection transform expects an iterable, "%s" given.', get_debug_type($value)));
        }

        $values = [];
        foreach ($value as $v) {
            $values[] = $this->objectMapper->map($v);
        }

        return $values;
    }
}
