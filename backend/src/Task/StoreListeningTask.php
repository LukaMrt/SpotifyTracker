<?php

declare(strict_types=1);

namespace App\Task;

use App\Service\ListeningService;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: StoreListeningTask::INTERVAL)]
class StoreListeningTask
{
    protected const string INTERVAL = '30 seconds';
    
    protected const string TIMEZONE = 'Europe/Paris';

    public function __construct(
        protected readonly ListeningService $listeningService,
    ) {
    }

    public function __invoke(): void
    {
        $this->listeningService->storeCurrentTrack();
    }
}
