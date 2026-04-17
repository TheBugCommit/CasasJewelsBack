<?php

namespace App\Application\Queue;

use App\Application\Model\EmailJob;

interface EmailQueueInterface
{
    public function push(EmailJob $job): void;
    public function pop(): ?EmailJob;
    /** @return EmailJob[] */
    public function popMultiple(int $count): array;

    public function size(): int;
}
