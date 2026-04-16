<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\EmailJob;

interface EmailJobRepositoryInterface
{
    public function enqueue(EmailJob $job): void;

    public function dequeue(): ?EmailJob;
    public function dequeueMultiple(int $count): array;

    public function size(): int;
}
