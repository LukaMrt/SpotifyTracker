<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Listening;

interface ListeningRepositoryInterface
{
    public function save(Listening $listening): void;
}
