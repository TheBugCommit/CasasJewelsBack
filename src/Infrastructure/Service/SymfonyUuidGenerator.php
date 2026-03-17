<?php

namespace App\Infrastructure\Service;

use App\Domain\Service\UuidGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class SymfonyUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
