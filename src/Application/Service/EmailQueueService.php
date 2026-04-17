<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Model\EmailJob;
use App\Application\Model\EmailJobType;
use App\Application\Queue\EmailQueueInterface;
use App\Domain\Service\UuidGeneratorInterface;

final class EmailQueueService
{
    public function __construct(
        private readonly EmailQueueInterface    $emailQueue,
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {}

    public function queueOrderCreated(string $orderId): void
    {
        $this->emailQueue->push(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderCreated,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function queueOrderDelivering(string $orderId): void
    {
        $this->emailQueue->push(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderDelivering,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function queueOrderDelivered(string $orderId): void
    {
        $this->emailQueue->push(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderDelivered,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function queueOrderPaid(string $orderId): void
    {
        $this->emailQueue->push(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderPaid,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function queueOrderCancelled(string $orderId): void
    {
        $this->emailQueue->push(new EmailJob(
            id:         $this->uuidGenerator->generate(),
            type:       EmailJobType::OrderCancelled,
            orderId:    $orderId,
            retryCount: 0,
        ));
    }

    public function requeue(EmailJob $job): void
    {
        $this->emailQueue->push($job->withIncrementedRetry());
    }
}
