<?php

namespace App\Domain\Auth;

use App\Domain\ValueObject\UserRole;

interface UserCredentialsInterface
{
    public function getEmail(): string;

    public function getName(): string;
    public function getPassword(): string;
    public function getRole(): UserRole;
}
