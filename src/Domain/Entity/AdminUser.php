<?php

namespace App\Domain\Entity;
use App\Domain\Auth\UserCredentialsInterface;
use App\Domain\ValueObject\UserRole;

class AdminUser implements UserCredentialsInterface
{
    private string $id;
    private string $email;
    private string $name;
    private string $password;

    public function __construct(string $id, string $name, string $email, string $password)
    {
        $this->id       = $id;
        $this->name     = $name;
        $this->email    = $email;
        $this->password = $password;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): UserRole
    {
        return UserRole::ADMIN;
    }
}
