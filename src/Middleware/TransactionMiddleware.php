<?php

namespace App\Middleware;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TransactionMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected readonly Connection $connection,
    ) {
    }

    #[\Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->connection->beginTransaction();

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->connection->commit();
        return $envelope;
    }
}