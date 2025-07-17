<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class ListeningCount
{
    public function __construct(
        protected readonly string $name,
        protected readonly int $count,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
