<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\EmailJobType;

final class EmailJob
{
    public const int MAX_RETRIES = 2;
    public function __construct(
        public readonly string       $id,
        public readonly EmailJobType $type,
        public readonly string       $orderId,
        public readonly int          $retryCount = 0,
    ) {}

    public function canBeRetried(): bool
    {
        return $this->retryCount < self::MAX_RETRIES;
    }
    public function withIncrementedRetry(): self
    {
        if (!$this->canBeRetried()) {
            throw new \DomainException("Can't retry {$this->id}.");
        }

        return new self(
            id:         $this->id,
            type:       $this->type,
            orderId:    $this->orderId,
            retryCount: $this->retryCount + 1,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type->value,
            'orderId'    => $this->orderId,
            'retryCount' => $this->retryCount,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:         $data['id'],
            type:       EmailJobType::from($data['type']),
            orderId:    $data['orderId'],
            retryCount: $data['retryCount'] ?? 0,
        );
    }
}
