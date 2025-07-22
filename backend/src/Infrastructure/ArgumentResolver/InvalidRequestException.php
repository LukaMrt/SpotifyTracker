<?php

declare(strict_types=1);

namespace App\Infrastructure\ArgumentResolver;

use Symfony\Component\HttpFoundation\Response;

class InvalidRequestException extends \InvalidArgumentException
{
    public function __construct(string $message = 'Wrong request', int $code = Response::HTTP_BAD_REQUEST, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
