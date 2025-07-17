<?php

declare(strict_types=1);

namespace App\Task;

use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractTask
{
    public function __construct(
        protected readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(): void
    {
        $this->messageBus->dispatch($this->getMessage());
    }

    protected abstract function getMessage(): object;
}
