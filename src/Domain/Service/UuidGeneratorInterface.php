<?php

namespace App\Domain\Service;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
