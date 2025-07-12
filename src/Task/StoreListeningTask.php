<?php

declare(strict_types=1);

namespace App\Task;

use App\Domain\Message\StoreListening;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: StoreListeningTask::INTERVAL)]
class StoreListeningTask extends AbstractTask
{
    protected const string INTERVAL = '30 seconds';
    
    protected const string TIMEZONE = 'Europe/Paris';

    #[\Override]
    protected function getMessage(): object
    {
        return new StoreListening(new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone(self::TIMEZONE)));
    }
}
