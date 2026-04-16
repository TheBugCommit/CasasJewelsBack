<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\EmailJob;
use App\Domain\Repository\EmailJobRepositoryInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\EmailJobType;

final class EmailQueueService
{
    public function __construct(
        private readonly EmailJobRepositoryInterface $emailJobRepository,
        private readonly UuidGeneratorInterface      $uuidGenerator,
    ) {}

    public function queueOrderCreated(string $orderId): void
    {
        $this->emailJobRepository->enqueue(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderCreated,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function queueOrderDelivering(string $orderId): void
    {
        $this->emailJobRepository->enqueue(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderDelivering,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function queueOrderDelivered(string $orderId): void
    {
        $this->emailJobRepository->enqueue(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderDelivered,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function requeue(EmailJob $job): void
    {
        $this->emailJobRepository->enqueue($job->withIncrementedRetry());
    }
}
