<?php

namespace App\Domain\Repository;

interface UnitOfWorkInterface
{
    public function commit(): void;
}
