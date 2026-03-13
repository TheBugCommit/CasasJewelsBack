<?php

namespace App\Domain\Repository;

use App\Domain\Entity\AdminUser;

interface AdminUserRepositoryInterface
{
    public function findByEmail(string $email): ?AdminUser;
}
