<?php
declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Application\Model\EmailJob;
use App\Application\Queue\EmailQueueInterface;
use App\Domain\ValueObject\EmailJobType;

final class RedisEmailQueue implements EmailQueueInterface
{
    private const QUEUE_KEY = 'email_queue';

    public function __construct(
        private readonly \Redis $redis,
    ) {}

    public function push(EmailJob $job): void
    {
        $payload = json_encode([
            'id'         => $job->id,
            'type'       => $job->type->value,
            'orderId'    => $job->orderId,
            'retryCount' => $job->retryCount,
        ]);

        $this->redis->rpush(self::QUEUE_KEY, $payload);
    }

    public function pop(): ?EmailJob
    {
        $raw = $this->redis->lpop(self::QUEUE_KEY);

        if (!is_string($raw)) {
            return null;
        }

        return $this->deserializeJob($raw);
    }

    public function popMultiple(int $count): array
    {
        $rawItems = $this->redis->lpop(self::QUEUE_KEY, $count);

        if (!$rawItems || !is_array($rawItems)) {
            return [];
        }

        $jobs = [];
        foreach ($rawItems as $raw) {
            $job = $this->deserializeJob($raw);
            if ($job !== null) {
                $jobs[] = $job;
            }
        }

        return $jobs;
    }

    public function size(): int
    {
        return (int) $this->redis->llen(self::QUEUE_KEY);
    }

    private function deserializeJob(string $raw): ?EmailJob
    {
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        return new EmailJob(
            id:         $data['id'],
            type:       EmailJobType::from($data['type']),
            orderId:    $data['orderId'],
            retryCount: $data['retryCount'] ?? 0,
        );
    }
}
