<?php

namespace App\Task;

use App\Domain\Message\StoreListening;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(
    frequency: '5 second',
)]
class StoreListeningTask extends AbstractTask
{
    protected function getMessage(): object
    {
        return new StoreListening(new \DateTimeImmutable());
    }
}