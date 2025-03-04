<?php

namespace App\Domain\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
class StoreListening
{
    public function __construct(
        public readonly \DateTimeImmutable $date
    ) {
    }
}
