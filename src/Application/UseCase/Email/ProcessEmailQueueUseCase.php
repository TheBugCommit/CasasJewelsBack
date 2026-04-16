<?php

declare(strict_types=1);

namespace App\Application\UseCase\Email;

use App\Domain\Model\EmailJob;
use App\Domain\Repository\EmailJobRepositoryInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Application\Service\EmailQueueService;
use App\Domain\Service\EmailServiceInterface;
use App\Domain\ValueObject\EmailJobType;
use Psr\Log\LoggerInterface;

final class ProcessEmailQueueUseCase
{
    private const int MAX_EMAILS = 1000;

    public function __construct(
        private readonly EmailJobRepositoryInterface $emailJobRepository,
        private readonly EmailQueueService           $emailQueueService,
        private readonly OrderRepositoryInterface    $orders,
        private readonly EmailServiceInterface       $emailService,
        private readonly LoggerInterface             $logger,
    ) {}

    /**
     * @return array{processed: int, failed: int}
     */
    public function execute(): array
    {
        $jobs = $this->emailJobRepository->dequeueMultiple(ProcessEmailQueueUseCase::MAX_EMAILS);
        if (empty($jobs)) {
            return ['processed' => 0, 'failed' => 0];
        }

        $orderIds = array_unique(array_map(fn(EmailJob $job) => $job->orderId, $jobs));
        $ordersFromDb = $this->orders->findByIds($orderIds);

        $ordersById = [];
        foreach ($ordersFromDb as $order) {
            $ordersById[$order->getId()] = $order;
        }

        $processed = 0;
        $failed = 0;

        foreach ($jobs as $job) {
            $order = $ordersById[$job->orderId] ?? null;

            if (!$order) {
                $this->logger->warning("Order {$job->orderId} not found — job {$job->id} discarded.");
                $failed++;
                continue;
            }

            try {
                match ($job->type) {
                    EmailJobType::OrderCreated    => $this->emailService->sendOrderCreated($order),
                    EmailJobType::OrderDelivering => $this->emailService->sendOrderDelivering($order),
                    EmailJobType::OrderDelivered  => $this->emailService->sendOrderDelivered($order),
                };
                $processed++;
            } catch (\Throwable $e) {
                $failed++;

                try {
                    $this->emailQueueService->requeue($job);
                    $this->logger->error("Job {$job->id} failed, re-enqueued: {$e->getMessage()}");
                } catch (\DomainException $e) {
                    $this->logger->critical("Job {$job->id} failed after retry: {$e->getMessage()}");
                }
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }
}
