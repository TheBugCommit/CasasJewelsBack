<?php

namespace App\Domain\ValueObject;

enum UserRole: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
}
