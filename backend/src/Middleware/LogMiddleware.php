<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class LogMiddleware implements MiddlewareInterface
{

    public function __construct(
        protected readonly LoggerInterface     $logger,
    ) {
    }

    #[\Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->logger->info('Handling message', [
            'message' => $envelope->getMessage(),
        ]);

        try {
            $result = $stack->next()->handle($envelope, $stack);
            $this->logger->info('Handled message', [
                'message' => $envelope->getMessage(),
            ]);
            return $result;
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed handling message', [
                'message' => $envelope->getMessage(),
                'exception_message' => $throwable->getMessage(),
                'exception_trace' => $throwable->getTraceAsString(),
            ]);
            throw $throwable;
        }
    }
}
