<?php

declare(strict_types=1);

namespace App\Application\Queue;

use App\Application\Model\EmailJob;
use App\Application\Model\EmailJobType;

final class EmailJobMapper
{
    public function toJson(EmailJob $job): string
    {
        return json_encode([
            'id'         => $job->id,
            'type'       => $job->type->value,
            'orderId'    => $job->orderId,
            'retryCount' => $job->retryCount,
        ], JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): ?EmailJob
    {
        $data = json_decode($json, true);
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
