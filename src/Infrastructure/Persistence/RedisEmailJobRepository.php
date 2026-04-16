<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Model\EmailJob;
use App\Domain\Repository\EmailJobRepositoryInterface;

final class RedisEmailJobRepository implements EmailJobRepositoryInterface
{
    private const QUEUE_KEY = 'email_queue';

    public function __construct(
        private readonly \Redis $redis,
    ) {}

    public function enqueue(EmailJob $job): void
    {
        $this->redis->rpush(self::QUEUE_KEY, json_encode($job->toArray()));
    }

    public function dequeue(): ?EmailJob
    {
        $raw = $this->redis->lpop(self::QUEUE_KEY);

        if (!is_string($raw)) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        return EmailJob::fromArray($data);
    }

    public function dequeueMultiple(int $count): array
    {
        $rawItems = $this->redis->lpop(self::QUEUE_KEY, $count);

        if (!$rawItems || !is_array($rawItems)) {
            return [];
        }

        $jobs = [];
        foreach ($rawItems as $raw) {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $jobs[] = EmailJob::fromArray($data);
            }
        }

        return $jobs;
    }

    public function size(): int
    {
        return (int) $this->redis->llen(self::QUEUE_KEY);
    }
}
