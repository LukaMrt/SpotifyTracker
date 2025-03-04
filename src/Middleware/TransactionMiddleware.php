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

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->connection->beginTransaction();

        $envelope = $stack->next()->handle($envelope, $stack);

        $this->connection->commit();
        return $envelope;
    }
}